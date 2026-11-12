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

        $employees = $query->orderBy('name')->paginate(12)->withQueryString();

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

        return view('portal.employees.index', [
            'employees' => $employees,
            'attendanceStats' => $attendanceStats,
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
        $this->authorizeAdmin($request);

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
        $this->authorizeAdmin($request);

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
        $this->authorizeAdmin($request);

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
        $this->authorizeAdmin($request);

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
        $this->authorizeAdmin($request);

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

