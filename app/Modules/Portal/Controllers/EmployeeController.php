<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Modules\Attendance\Services\AttendanceService;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    /**
     * Predefined company divisions
     */
    public const DIVISIONS = [
        'NOC',
        'Teknik',
        'Umum',
        'Finance & Akuntansi',
        'IT & Sistem',
        'HRD & Human Capital',
        'Executive',
    ];

    /**
     * Supported payroll banks
     */
    public const BANKS = [
        'BCA',
        'Bank Mandiri',
        'BNI',
        'BRI',
        'BSI (Bank Syariah Indonesia)',
        'CIMB Niaga',
        'BJB (Bank Jabar Banten)',
    ];

    /**
     * Check if the authenticated user has administrative HRIS access
     */
    protected function authorizeAdmin(Request $request): void
    {
        $user = $request->user();
        abort_unless(
            $user && ($user->hasRole(['super_admin', 'admin', 'hrd']) || $user->can('attendance.employee.manage')),
            403,
            'Akses ditolak. Fitur manajemen data karyawan hanya untuk Administrator dan HRD.'
        );
    }

    /**
     * Check if the authenticated user has write/edit permissions for employees.
     * HRD has read-only view permission; only Super Admin, Admin, or authorized managers can modify.
     */
    protected function authorizeWrite(Request $request): void
    {
        $user = $request->user();
        abort_unless(
            $user && ($user->hasRole(['super_admin', 'admin']) || ($user->can('attendance.employee.manage') && ! $user->hasRole('hrd'))),
            403,
            'Akses ditolak. Role HRD hanya memiliki izin melihat data karyawan (Read-Only). Aksi penambahan/pengubahan hanya dapat dilakukan oleh Administrator.'
        );
    }

    /**
     * Display a listing of employees with search and filters
     */
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $search = trim($request->string('search')->toString());
        $division = $request->string('division')->toString();
        $role = $request->string('role')->toString();
        $status = $request->string('status', 'active')->toString();

        $query = User::query()->with(['roles', 'supervisor', 'faceTemplate']);

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%");
            });
        }

        if (! empty($division)) {
            $query->where('division', $division);
        }

        if (! empty($role)) {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $nowJakarta = now('Asia/Jakarta');
        $month = (int) $request->input('month', $nowJakarta->month);
        $year = (int) $request->input('year', $nowJakarta->year);

        // Sort priority: admin (1), ceo (2), hrd (3), finance (4), atasan (5), karyawan (6)
        $roleRankSub = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->whereColumn('model_has_roles.model_id', 'users.id')
            ->where('model_has_roles.model_type', User::class)
            ->selectRaw("MIN(CASE 
                WHEN roles.name IN ('super_admin', 'admin') THEN 1
                WHEN roles.name = 'ceo' THEN 2
                WHEN roles.name = 'hrd' THEN 3
                WHEN roles.name = 'finance' THEN 4
                WHEN roles.name LIKE 'atasan%' OR roles.name = 'manager_teknisi' THEN 5
                ELSE 6
            END)");

        $employees = $query
            ->select('users.*')
            ->selectSub($roleRankSub, 'role_rank')
            ->orderByRaw('COALESCE(role_rank, 6) ASC')
            ->orderBy('users.name', 'ASC')
            ->paginate(12)
            ->withQueryString();

        // Monthly attendance statistics per employee for HRD & Admin overview
        $employeeIds = $employees->pluck('id')->all();
        $monthlyAttendances = Attendance::query()
            ->whereIn('user_id', $employeeIds)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->groupBy('user_id');

        $attendanceStats = [];
        foreach ($employeeIds as $id) {
            $userAtts = $monthlyAttendances->get($id, collect());
            $totalHadir = $userAtts->whereIn('status', ['present', 'late'])->count();
            $tepatWaktu = $userAtts->where('status', 'present')->count();
            $terlambat = $userAtts->where('status', 'late')->count();
            $alpa = $userAtts->where('status', 'absent')->count();
            $cuti = $userAtts->whereIn('status', ['leave', 'sick', 'special'])->count();
            $totalMinutes = $userAtts->sum('work_duration_minutes');
            $jamKerja = round($totalMinutes / 60, 1);

            $attendanceStats[$id] = [
                'total_hadir' => $totalHadir,
                'tepat_waktu' => $tepatWaktu,
                'terlambat' => $terlambat,
                'alpa' => $alpa,
                'cuti' => $cuti,
                'jam_kerja' => $jamKerja,
            ];
        }

        // Statistics
        $totalEmployees = User::count();
        $activeEmployees = User::where('is_active', true)->count();
        $inactiveEmployees = User::where('is_active', false)->count();

        $divisionCounts = User::query()
            ->where('is_active', true)
            ->whereNotNull('division')
            ->select('division', DB::raw('count(*) as count'))
            ->groupBy('division')
            ->pluck('count', 'division')
            ->toArray();

        // Supervisor Candidates (All users with managerial/admin roles)
        $supervisors = User::query()
            ->where('is_active', true)
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', [
                    'super_admin', 'admin', 'hrd', 'ceo',
                    'atasan', 'atasan_noc', 'atasan_teknik', 'atasan_umum', 'manager_teknisi'
                ]);
            })
            ->orderBy('name')
            ->get();

        $roles = Role::query()->orderBy('name')->get();
        $canWrite = $request->user()->hasRole(['super_admin', 'admin']) || ($request->user()->can('attendance.employee.manage') && ! $request->user()->hasRole('hrd'));

        return view('portal.employees.index', [
            'employees' => $employees,
            'attendanceStats' => $attendanceStats,
            'canWrite' => $canWrite,
            'month' => $month,
            'year' => $year,
            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'inactiveEmployees' => $inactiveEmployees,
            'divisionCounts' => $divisionCounts,
            'supervisors' => $supervisors,
            'roles' => $roles,
            'divisions' => self::DIVISIONS,
            'banks' => self::BANKS,
            'currentFilters' => [
                'search' => $search,
                'division' => $division,
                'role' => $role,
                'status' => $status,
                'month' => $month,
                'year' => $year,
            ],
        ]);
    }

    /**
     * Store a newly created employee in storage
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeWrite($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'employee_code' => ['nullable', 'string', 'max:32', 'unique:users,employee_code'],
            'username' => ['nullable', 'string', 'max:64', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:32', 'unique:users,phone'],
            'nik' => ['nullable', 'string', 'max:32', 'unique:users,nik'],
            'division' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'join_date' => ['nullable', 'date'],
            'password' => ['nullable', 'string', 'min:6'],
            'bank' => ['nullable', 'string', 'max:100'],
            'no_rekening' => ['nullable', 'string', 'max:50'],
            'atas_nama' => ['nullable', 'string', 'max:100'],
            'potongan_alpa' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'foto' => ['nullable', 'image', 'max:5120'],
        ]);

        // Auto-generate employee code if not provided
        $employeeCode = $validated['employee_code'] ?? null;
        if (empty($employeeCode)) {
            $employeeCode = $this->generateNextEmployeeCode();
        }

        // Auto-generate username if not provided
        $username = $validated['username'] ?? null;
        if (empty($username)) {
            $username = strtolower($employeeCode);
            // Verify unique
            if (User::where('username', $username)->exists()) {
                $username = strtolower(Str::slug($validated['name'], '')) . rand(10, 99);
            }
        }

        // Password handling
        $mustChangePassword = false;
        if (! empty($validated['password'])) {
            $passwordHash = Hash::make($validated['password']);
        } else {
            $passwordHash = Hash::make('msn12345');
            $mustChangePassword = true;
        }

        // Photo upload
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('avatars', 'public');
        }

        $user = User::create([
            'name' => $validated['name'],
            'employee_code' => $employeeCode,
            'username' => $username,
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'nik' => $validated['nik'] ?? null,
            'division' => $validated['division'] ?? null,
            'position' => $validated['position'] ?? null,
            'supervisor_id' => $validated['supervisor_id'] ?? null,
            'join_date' => $validated['join_date'] ?? now()->toDateString(),
            'potongan_alpa' => $validated['potongan_alpa'] ?? null,
            'bank' => $validated['bank'] ?? null,
            'no_rekening' => $validated['no_rekening'] ?? null,
            'atas_nama' => $validated['atas_nama'] ?? $validated['name'],
            'foto' => $fotoPath,
            'password' => $passwordHash,
            'must_change_password' => $mustChangePassword,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->assignRole($validated['role']);

        AuditLogger::record(
            action: 'employee.created',
            auditable: $user,
            newValues: [
                'employee_code' => $user->employee_code,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $validated['role'],
                'division' => $user->division,
            ],
            reason: 'Penambahan data karyawan baru oleh Admin',
            actorId: $request->user()->id
        );

        return redirect()->route('portal.employees.index')
            ->with('status', "Karyawan '{$user->name}' ({$user->employee_code}) berhasil ditambahkan ke sistem.");
    }

    /**
     * Update the specified employee in storage
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeWrite($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'employee_code' => ['required', 'string', 'max:32', Rule::unique('users', 'employee_code')->ignore($user->id)],
            'username' => ['required', 'string', 'max:64', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:32', Rule::unique('users', 'phone')->ignore($user->id)],
            'nik' => ['nullable', 'string', 'max:32', Rule::unique('users', 'nik')->ignore($user->id)],
            'division' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'supervisor_id' => ['nullable', 'exists:users,id', 'different:id'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'join_date' => ['nullable', 'date'],
            'password' => ['nullable', 'string', 'min:6'],
            'bank' => ['nullable', 'string', 'max:100'],
            'no_rekening' => ['nullable', 'string', 'max:50'],
            'atas_nama' => ['nullable', 'string', 'max:100'],
            'potongan_alpa' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'foto' => ['nullable', 'image', 'max:5120'],
        ]);

        $userData = [
            'name' => $validated['name'],
            'employee_code' => $validated['employee_code'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'nik' => $validated['nik'] ?? null,
            'division' => $validated['division'] ?? null,
            'position' => $validated['position'] ?? null,
            'supervisor_id' => $validated['supervisor_id'] ?? null,
            'join_date' => $validated['join_date'] ?? $user->join_date,
            'potongan_alpa' => $validated['potongan_alpa'] ?? $user->potongan_alpa,
            'bank' => $validated['bank'] ?? null,
            'no_rekening' => $validated['no_rekening'] ?? null,
            'atas_nama' => $validated['atas_nama'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ];

        // Handle password update if provided
        if (! empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
            $userData['password_changed_at'] = now();
        }

        // Handle photo upload
        if ($request->hasFile('foto')) {
            if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }
            $userData['foto'] = $request->file('foto')->store('avatars', 'public');
        }

        $oldValues = [
            'name' => $user->name,
            'division' => $user->division,
            'role' => $user->getRoleNames()->first(),
            'is_active' => $user->is_active,
        ];

        $user->update($userData);
        $user->syncRoles([$validated['role']]);

        AuditLogger::record(
            action: 'employee.updated',
            auditable: $user,
            oldValues: $oldValues,
            newValues: [
                'name' => $user->name,
                'division' => $user->division,
                'role' => $validated['role'],
                'is_active' => $user->is_active,
            ],
            reason: 'Pembaruan data karyawan oleh Admin',
            actorId: $request->user()->id
        );

        return redirect()->route('portal.employees.index')
            ->with('status', "Data karyawan '{$user->name}' berhasil diperbarui.");
    }

    /**
     * Remove or deactivate the specified employee
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorizeWrite($request);

        // Prevent self-deletion
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menghapus atau menonaktifkan akun Anda sendiri!');
        }

        $name = $user->name;
        $code = $user->employee_code;

        // Perform soft delete
        $user->is_active = false;
        $user->save();
        $user->delete();

        AuditLogger::record(
            action: 'employee.deleted',
            auditable: $user,
            oldValues: ['name' => $name, 'employee_code' => $code],
            reason: 'Penghapusan (Soft Delete) data karyawan oleh Admin',
            actorId: $request->user()->id
        );

        return redirect()->route('portal.employees.index')
            ->with('status', "Karyawan '{$name}' ({$code}) berhasil dihapus / dinonaktifkan.");
    }

    /**
     * Quick reset employee password
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->authorizeWrite($request);

        $defaultPassword = 'msn' . rand(1000, 9999);
        $user->update([
            'password' => Hash::make($defaultPassword),
            'must_change_password' => true,
        ]);

        AuditLogger::record(
            action: 'employee.password_reset',
            auditable: $user,
            reason: 'Reset password darurat oleh Admin',
            actorId: $request->user()->id
        );

        return back()->with('status', "Password untuk '{$user->name}' berhasil direset menjadi: {$defaultPassword} (Pengguna wajib mengganti password saat login).");
    }

    /**
     * Reset employee biometric face registration so they can re-enroll on /attendance
     */
    public function resetFace(Request $request, User $user, AttendanceService $attendanceService): RedirectResponse
    {
        $this->authorizeWrite($request);

        $reason = trim($request->string('reason')->toString());
        if ($reason === '') {
            $reason = 'Reset pendaftaran wajah absen oleh Admin melalui halaman Data Karyawan';
        }

        $attendanceService->resetUserFace($user, $reason, $request->user()->id);

        return back()->with('status', "Pendaftaran wajah absen untuk '{$user->name}' ({$user->employee_code}) berhasil direset. Karyawan dapat melakukan registrasi wajah ulang di halaman Absen.");
    }

    /**
     * Stream user avatar photo directly from Storage::disk('public')
     */
    public function avatar(User $user)
    {
        if (empty($user->foto)) {
            abort(404);
        }

        $cleanPath = ltrim($user->foto, '/');
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }

        if (Storage::disk('public')->exists($cleanPath)) {
            return response()->file(Storage::disk('public')->path($cleanPath), [
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        if (file_exists(public_path('storage/' . $cleanPath))) {
            return response()->file(public_path('storage/' . $cleanPath), [
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        if (file_exists(public_path($cleanPath))) {
            return response()->file(public_path($cleanPath), [
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        abort(404);
    }

    /**
     * Get employee quick summary (Attendance, Overtime, Submissions) for modal dialog.
     */
    public function summary(Request $request, User $user)
    {
        $this->authorizeAdmin($request);

        $nowJakarta = now('Asia/Jakarta');
        $rawMonth = $request->input('month');
        $rawYear = $request->input('year');

        $isAllMonth = ($rawMonth === 'all' || $rawMonth === '' || $rawMonth === null);
        $isAllYear = ($rawYear === 'all' || $rawYear === '' || $rawYear === null);

        $month = $isAllMonth ? 'all' : (int) $rawMonth;
        $year = $isAllYear ? 'all' : (int) $rawYear;

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        if ($isAllMonth && $isAllYear) {
            $periodLabel = 'Semua Periode';
        } elseif ($isAllMonth) {
            $periodLabel = 'Semua Bulan ' . $year;
        } elseif ($isAllYear) {
            $periodLabel = ($monthNames[$month] ?? 'Bulan ' . $month) . ' (Semua Tahun)';
        } else {
            $periodLabel = ($monthNames[$month] ?? 'Bulan ' . $month) . ' ' . $year;
        }

        // 1. Absensi Stats & Items
        $attQuery = Attendance::query()->where('user_id', $user->id);
        if (! $isAllYear) {
            $attQuery->whereYear('date', $year);
        }
        if (! $isAllMonth) {
            $attQuery->whereMonth('date', $month);
        }
        $attendances = $attQuery->orderByDesc('date')->get();

        $totalHadir = $attendances->whereIn('status', ['present', 'late'])->count();
        $tepatWaktu = $attendances->where('status', 'present')->count();
        $terlambat = $attendances->where('status', 'late')->count();
        $alpa = $attendances->where('status', 'absent')->count();
        $cuti = $attendances->whereIn('status', ['leave', 'sick', 'special'])->count();
        $totalMinutes = $attendances->sum('work_duration_minutes');
        $jamKerja = round($totalMinutes / 60, 1);

        $attLimit = ($isAllMonth || $isAllYear) ? 50 : 31;
        $attendanceItems = $attendances->take($attLimit)->map(function ($att) {
            return [
                'id' => $att->id,
                'date' => Carbon::parse($att->date)->translatedFormat('d M Y'),
                'clock_in' => $att->clock_in ? Carbon::parse($att->clock_in)->format('H:i') : '-',
                'clock_out' => $att->clock_out ? Carbon::parse($att->clock_out)->format('H:i') : '-',
                'status' => $att->status,
                'status_label' => match ($att->status) {
                    'present' => 'Tepat Waktu',
                    'late' => 'Terlambat',
                    'absent' => 'Alpa',
                    'leave' => 'Cuti',
                    'sick' => 'Sakit',
                    'special' => 'Izin',
                    default => ucfirst($att->status),
                },
                'duration' => $att->work_duration_minutes ? round($att->work_duration_minutes / 60, 1) . ' jam' : '-',
            ];
        });

        // 2. Lembur Stats & Items
        $lemburQuery = \App\Models\Lembur::query()->where('user_id', $user->id);
        if (! $isAllYear) {
            $lemburQuery->whereYear('tanggal', $year);
        }
        if (! $isAllMonth) {
            $lemburQuery->whereMonth('tanggal', $month);
        }
        $lemburs = $lemburQuery->orderByDesc('tanggal')->get();

        $totalLemburNominal = $lemburs->sum('nominal');

        $lemburLimit = ($isAllMonth || $isAllYear) ? 50 : 20;
        $lemburItems = $lemburs->take($lemburLimit)->map(function ($l) {
            return [
                'id' => $l->id,
                'nomor_lembur' => $l->nomor_lembur,
                'tanggal' => Carbon::parse($l->tanggal)->translatedFormat('d M Y'),
                'jenis_lembur' => $l->jenis_lembur ?: 'Lembur',
                'jam' => ($l->jam_mulai ? Carbon::parse($l->jam_mulai)->format('H:i') : '--:--') . ' - ' . ($l->jam_selesai ? Carbon::parse($l->jam_selesai)->format('H:i') : '--:--'),
                'durasi' => $l->durasi_jam_formatted ?? ($l->durasi_menit ? round($l->durasi_menit / 60, 1) . ' jam' : '-'),
                'nominal' => 'Rp ' . number_format($l->nominal, 0, ',', '.'),
                'status' => $l->status,
                'alasan' => $l->alasan ?: '-',
            ];
        });

        // 3. Pengajuan Stats & Items
        $pengajuanQuery = \App\Models\Pengajuan::query()->where('user_id', $user->id);
        if (! $isAllYear) {
            $pengajuanQuery->whereYear('created_at', $year);
        }
        if (! $isAllMonth) {
            $pengajuanQuery->whereMonth('created_at', $month);
        }
        $pengajuans = $pengajuanQuery->orderByDesc('created_at')->get();

        $totalPengajuanNominal = $pengajuans->sum('nominal_total');

        $pengajuanLimit = ($isAllMonth || $isAllYear) ? 50 : 20;
        $pengajuanItems = $pengajuans->take($pengajuanLimit)->map(function ($p) {
            return [
                'id' => $p->id,
                'nomor_pengajuan' => $p->nomor_pengajuan,
                'jenis' => ucfirst($p->jenis),
                'keperluan' => $p->keperluan ?: ($p->judul ?: '-'),
                'nominal' => 'Rp ' . number_format($p->nominal_total, 0, ',', '.'),
                'status' => $p->status,
                'tanggal' => $p->created_at ? Carbon::parse($p->created_at)->translatedFormat('d M Y') : '-',
            ];
        });

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'employee_code' => $user->employee_code ?: 'KR' . $user->id,
                'avatar_url' => $user->avatar_url,
                'initials' => $user->initials,
                'division' => $user->division ?: 'Non-Divisi',
                'position' => $user->position ?: 'Staff',
                'email' => $user->email,
                'phone' => $user->phone,
                'branch' => $user->branch ?? 'Bandung',
                'is_active' => (bool) $user->is_active,
                'role' => $user->roles->first()?->name ? strtoupper($user->roles->first()->name) : 'KARYAWAN',
            ],
            'period' => [
                'month' => $month,
                'year' => $year,
                'label' => $periodLabel,
            ],
            'attendance' => [
                'stats' => [
                    'total_hadir' => $totalHadir,
                    'tepat_waktu' => $tepatWaktu,
                    'terlambat' => $terlambat,
                    'alpa' => $alpa,
                    'cuti' => $cuti,
                    'jam_kerja' => $jamKerja,
                ],
                'items' => $attendanceItems,
            ],
            'overtime' => [
                'total_count' => $lemburs->count(),
                'total_nominal' => 'Rp ' . number_format($totalLemburNominal, 0, ',', '.'),
                'items' => $lemburItems,
            ],
            'pengajuan' => [
                'total_count' => $pengajuans->count(),
                'total_nominal' => 'Rp ' . number_format($totalPengajuanNominal, 0, ',', '.'),
                'items' => $pengajuanItems,
            ],
            'links' => [
                'attendance' => route('attendance.history', array_filter([
                    'user_id' => $user->id,
                    'month' => $isAllMonth ? null : $month,
                    'year' => $isAllYear ? null : $year,
                ])),
                'overtime' => route('gomsn.overtime.index', array_filter([
                    'user_id' => $user->id,
                    'bulan' => $isAllMonth ? null : $month,
                    'tahun' => $isAllYear ? null : $year,
                ])),
                'pengajuan' => route('gomsn.pengajuan.index', ['tab' => 'all']),
            ],
        ]);
    }

    /**
     * Helper to auto-generate next employee code like KR00021
     */
    protected function generateNextEmployeeCode(): string
    {
        $latest = User::withTrashed()
            ->where('employee_code', 'like', 'KR%')
            ->orderByRaw('LENGTH(employee_code) DESC, employee_code DESC')
            ->first();

        if ($latest && preg_match('/KR(\d+)/i', $latest->employee_code, $matches)) {
            $nextNum = ((int) $matches[1]) + 1;
            return 'KR' . str_pad((string) $nextNum, 5, '0', STR_PAD_LEFT);
        }

        $count = User::withTrashed()->count() + 1;
        return 'KR' . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }
}

