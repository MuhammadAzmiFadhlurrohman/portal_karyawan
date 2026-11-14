<?php

use App\Modules\Attendance\Controllers\AttendanceController;
use App\Modules\Attendance\Controllers\AttendanceReportController;
use App\Modules\Attendance\Controllers\DeductionAndCorrectionController;
use App\Modules\Attendance\Controllers\LeaderboardController;
use App\Modules\Attendance\Controllers\LeaveController;
use App\Modules\Attendance\Controllers\ScheduleController;
use App\Modules\Attendance\Controllers\ShiftSwapController;
use App\Modules\Gomsn\Controllers\ChatbotController;
use App\Modules\Gomsn\Controllers\InventoryController;
use App\Modules\Gomsn\Controllers\KpiReportController;
use App\Modules\Gomsn\Controllers\NetworkOdpController;
use App\Modules\Gomsn\Controllers\OvertimeController;
use App\Modules\Gomsn\Controllers\PengajuanController;
use App\Modules\Gomsn\Controllers\TaskController;
use App\Modules\Portal\Controllers\AuthController;
use App\Modules\Portal\Controllers\EmployeeController;
use App\Modules\Portal\Controllers\PortalHubController;
use App\Modules\Ticketing\Controllers\ChronologyController;
use App\Modules\Ticketing\Controllers\HandoverController;
use App\Modules\Ticketing\Controllers\StopClockController;
use App\Modules\Ticketing\Controllers\TechnicalAssetController;
use App\Modules\Ticketing\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // First-time password change & OTP device approval (§6.1)
    Route::get('/auth/password-change', [AuthController::class, 'showFirstPasswordChange'])->name('password.first-change');
    Route::post('/auth/password-change', [AuthController::class, 'updateFirstPassword'])->name('password.first-change.update');
    Route::get('/auth/device-otp', [AuthController::class, 'showDeviceOtp'])->name('auth.device.otp');
    Route::post('/auth/device-otp', [AuthController::class, 'verifyDeviceOtp'])->name('auth.device.otp.verify');

    // Portal Hub & Foundation Administration (§4, §6)
    Route::get('/', [PortalHubController::class, 'index'])->name('portal.hub');
    Route::get('/portal/profile', [PortalHubController::class, 'profile'])->name('portal.profile');
    Route::put('/portal/profile', [PortalHubController::class, 'updateProfile'])->name('portal.profile.update');
    Route::get('/portal/sessions', [PortalHubController::class, 'sessionsAndDevices'])->name('portal.sessions');
    Route::post('/portal/sessions/{userSession}/revoke', [PortalHubController::class, 'revokeSession'])->name('portal.sessions.revoke');
    Route::post('/portal/devices/{userDevice}/approve', [PortalHubController::class, 'approveDevice'])->name('portal.devices.approve');
    Route::post('/portal/delegations', [PortalHubController::class, 'storeDelegation'])->name('portal.delegations.store');

    Route::get('/portal/settings', [PortalHubController::class, 'systemSettings'])->name('portal.settings');
    Route::post('/portal/settings', [PortalHubController::class, 'updateSystemSetting'])->name('portal.settings.update');

    Route::get('/portal/audit-logs', [PortalHubController::class, 'auditLogs'])->name('portal.audit');

    // Manajemen Karyawan (§6, Admin / HRD) & Avatar Streaming
    Route::get('/portal/users/{user}/avatar', [EmployeeController::class, 'avatar'])->name('portal.user.avatar');
    Route::get('/portal/employees', [EmployeeController::class, 'index'])->name('portal.employees.index');
    Route::get('/portal/employees/{user}/summary', [EmployeeController::class, 'summary'])->name('portal.employees.summary');
    Route::post('/portal/employees', [EmployeeController::class, 'store'])->name('portal.employees.store');
    Route::put('/portal/employees/{user}', [EmployeeController::class, 'update'])->name('portal.employees.update');
    Route::delete('/portal/employees/{user}', [EmployeeController::class, 'destroy'])->name('portal.employees.destroy');
    Route::post('/portal/employees/{user}/reset-password', [EmployeeController::class, 'resetPassword'])->name('portal.employees.reset-password');
    Route::post('/portal/employees/{user}/reset-face', [EmployeeController::class, 'resetFace'])->name('portal.employees.reset-face');

    // Fase 1: Modul Absensi Inti — Biometrik Wajah, Geofencing & Jadwal Shift (§5.1, §5.2, §5.9)
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
    Route::get('/attendance/employee-recap', [\App\Modules\Attendance\Controllers\EmployeeAttendanceRecapController::class, 'index'])->name('attendance.employee-recap');
    Route::get('/attendance/employee-recap/{user}', [\App\Modules\Attendance\Controllers\EmployeeAttendanceRecapController::class, 'show'])->name('attendance.employee-recap.show');
    Route::get('/attendance/reports', [AttendanceReportController::class, 'index'])->name('attendance.reports');
    Route::get('/attendance/reports/export', [AttendanceReportController::class, 'exportCsv'])->name('attendance.reports.export');
    Route::post('/attendance/reports/update', [AttendanceReportController::class, 'updateRecord'])->name('attendance.reports.update');
    Route::post('/attendance/reports/quick-status', [AttendanceReportController::class, 'quickStatus'])->name('attendance.reports.quick-status');
    Route::post('/attendance/enroll', [AttendanceController::class, 'enroll'])->name('attendance.enroll');
    Route::post('/attendance/reset-face', [AttendanceController::class, 'resetFace'])->name('attendance.reset-face');
    Route::post('/attendance/attempt-token', [AttendanceController::class, 'issueAttemptToken'])->name('attendance.attempt-token');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
    Route::post('/attendance/offline-sync', [AttendanceController::class, 'syncOffline'])->name('attendance.offline-sync');

    Route::get('/attendance/schedules', [ScheduleController::class, 'index'])->name('attendance.schedules');
    Route::get('/attendance/schedules/peer-shift', [ScheduleController::class, 'getPeerShift'])->name('attendance.schedules.peer-shift');
    Route::post('/attendance/schedules/generate', [ScheduleController::class, 'generate'])->name('attendance.schedules.generate');
    Route::post('/attendance/schedules/delete-month', [ScheduleController::class, 'deleteMonth'])->name('attendance.schedules.delete-month');
    Route::post('/attendance/schedules/holidays', [ScheduleController::class, 'storeHoliday'])->name('attendance.schedules.holidays.store');
    Route::delete('/attendance/schedules/holidays/{holiday}', [ScheduleController::class, 'deleteHoliday'])->name('attendance.schedules.holidays.destroy');
    Route::get('/attendance/schedules/template', [ScheduleController::class, 'downloadTemplate'])->name('attendance.schedules.template');
    Route::post('/attendance/schedules/upload-piket', [ScheduleController::class, 'uploadPiket'])->name('attendance.schedules.upload-piket');
    Route::post('/attendance/schedules/preview', [ScheduleController::class, 'previewGenerate'])->name('attendance.schedules.preview');
    Route::post('/attendance/schedules/commit', [ScheduleController::class, 'commitGenerate'])->name('attendance.schedules.commit');
    Route::post('/attendance/schedules/cell', [ScheduleController::class, 'updateCell'])->name('attendance.schedules.cell');
    Route::post('/attendance/offices', [ScheduleController::class, 'storeOffice'])->name('attendance.offices.store');
    Route::get('/attendance/schedules/export-csv', [ScheduleController::class, 'exportCsv'])->name('attendance.schedules.export');

    // Fase 2: Tukar Shift 4-Arah, Cuti & Sinkronisasi Alpa, Koreksi, Potongan & Leaderboard (§5.3–§5.7)
    Route::get('/attendance/swaps', [ShiftSwapController::class, 'index'])->name('attendance.swaps');
    Route::post('/attendance/swaps', [ShiftSwapController::class, 'store'])->name('attendance.swaps.store');
    Route::post('/attendance/swaps/{shiftSwap}/approve-target', [ShiftSwapController::class, 'approveTarget'])->name('attendance.swaps.approve-target');
    Route::post('/attendance/swaps/{shiftSwap}/approve-supervisor', [ShiftSwapController::class, 'approveSupervisor'])->name('attendance.swaps.approve-supervisor');
    Route::post('/attendance/swaps/{shiftSwap}/reject', [ShiftSwapController::class, 'reject'])->name('attendance.swaps.reject');

    Route::get('/attendance/leaves', [LeaveController::class, 'index'])->name('attendance.leaves');
    Route::post('/attendance/leaves', [LeaveController::class, 'store'])->name('attendance.leaves.store');
    Route::post('/attendance/leaves/{leave}/approve-supervisor', [LeaveController::class, 'approveSupervisor'])->name('attendance.leaves.approve-supervisor');
    Route::post('/attendance/leaves/{leave}/approve-hrd', [LeaveController::class, 'approveHrd'])->name('attendance.leaves.approve-hrd');
    Route::post('/attendance/leaves/{leave}/cancel', [LeaveController::class, 'cancel'])->name('attendance.leaves.cancel');
    Route::post('/attendance/leaves/override-balance', [LeaveController::class, 'overrideBalance'])->name('attendance.leaves.override-balance');

    Route::get('/attendance/deductions', [DeductionAndCorrectionController::class, 'index'])->name('attendance.deductions');
    Route::get('/attendance/corrections/approvals', [DeductionAndCorrectionController::class, 'approvals'])->name('attendance.corrections.approvals');
    Route::post('/attendance/corrections', [DeductionAndCorrectionController::class, 'submitCorrection'])->name('attendance.corrections.store');
    Route::post('/attendance/corrections/{attendanceCorrection}/approve-supervisor', [DeductionAndCorrectionController::class, 'approveSupervisor'])->name('attendance.corrections.approve-supervisor');
    Route::post('/attendance/corrections/{attendanceCorrection}/approve-hrd', [DeductionAndCorrectionController::class, 'approveHrd'])->name('attendance.corrections.approve-hrd');
    Route::post('/attendance/corrections/{attendanceCorrection}/reject', [DeductionAndCorrectionController::class, 'reject'])->name('attendance.corrections.reject');
    Route::post('/attendance/deductions/lock-period', [DeductionAndCorrectionController::class, 'lockPeriod'])->name('attendance.deductions.lock-period');
    Route::post('/attendance/deductions/employee-rate', [DeductionAndCorrectionController::class, 'updateEmployeeRate'])->name('attendance.deductions.update-employee-rate');

    Route::get('/attendance/leaderboard', [LeaderboardController::class, 'index'])->name('attendance.leaderboard');
    Route::post('/attendance/leaderboard/opt-out', [LeaderboardController::class, 'toggleOptOut'])->name('attendance.leaderboard.opt-out');
    Route::get('/attendance/leaderboard/export-csv', [LeaderboardController::class, 'exportCsv'])->name('attendance.leaderboard.export');

    // Fase 3: Modul Work Report & Tiketing Gangguan (§7, AC-04 s.d. AC-08, AC-11)
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/ims/search', [TicketController::class, 'searchIms'])->name('tickets.ims.search');
    Route::post('/tickets/backbone', [TicketController::class, 'storeBackbone'])->name('tickets.backbone.store');
    Route::post('/tickets/broadband', [TicketController::class, 'storeBroadband'])->name('tickets.broadband.store');
    Route::get('/tickets/{tiket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{tiket}/dispatch', [TicketController::class, 'dispatchTicket'])->name('tickets.dispatch');
    Route::post('/tickets/{tiket}/closing-awal', [TicketController::class, 'closingAwal'])->name('tickets.closing-awal');
    Route::post('/tickets/{tiket}/verify-close', [TicketController::class, 'verifyClose'])->name('tickets.verify-close');
    Route::post('/tickets/{tiket}/reject-closing', [TicketController::class, 'rejectClosing'])->name('tickets.reject-closing');
    Route::post('/tickets/{tiket}/reopen', [TicketController::class, 'reopenTicket'])->name('tickets.reopen');
    Route::post('/tickets/{tiket}/cancel', [TicketController::class, 'cancelTicket'])->name('tickets.cancel');

    // Chronology & Interval Monitoring (§7.3, AC-05)
    Route::post('/tickets/{tiket}/chronologies', [ChronologyController::class, 'store'])->name('tickets.chronologies.store');

    // Stop Clock Control (§7.4, AC-07)
    Route::post('/tickets/{tiket}/stop-clock', [StopClockController::class, 'store'])->name('tickets.stop-clock.store');
    Route::post('/tickets/stop-clock/{stopClock}/end', [StopClockController::class, 'end'])->name('tickets.stop-clock.end');
    Route::post('/tickets/stop-clock/{stopClock}/approve', [StopClockController::class, 'approve'])->name('tickets.stop-clock.approve');
    Route::post('/tickets/stop-clock/{stopClock}/reject', [StopClockController::class, 'reject'])->name('tickets.stop-clock.reject');

    // Technical Assets & Materials (§7.6, AC-06)
    Route::post('/tickets/{tiket}/materials', [TechnicalAssetController::class, 'useMaterial'])->name('tickets.materials.use');
    Route::post('/tickets/{tiket}/repair-points', [TechnicalAssetController::class, 'storeRepairPoint'])->name('tickets.repair-points.store');
    Route::post('/tickets/{tiket}/joint-closures', [TechnicalAssetController::class, 'storeJointClosure'])->name('tickets.joint-closures.store');
    Route::post('/tickets/joint-closures/{jointClosure}/cores', [TechnicalAssetController::class, 'storeClosureCore'])->name('tickets.closure-cores.store');
    Route::post('/tickets/{tiket}/maneuvers', [TechnicalAssetController::class, 'storeCoreManeuver'])->name('tickets.maneuvers.store');

    // Shift Handover (§7.5)
    Route::post('/tickets/{tiket}/handovers', [HandoverController::class, 'store'])->name('tickets.handovers.store');
    Route::post('/tickets/handovers/{handover}/acknowledge', [HandoverController::class, 'acknowledge'])->name('tickets.handovers.acknowledge');

    // ==========================================
    // Fase 4: GOMSN Operasional & Analitik
    // ==========================================

    // Pengajuan Dana & Barang (§8.1)
    Route::get('/gomsn/pengajuan', [PengajuanController::class, 'index'])->name('gomsn.pengajuan.index');
    Route::get('/gomsn/pengajuan/approvals', [PengajuanController::class, 'approvals'])->name('gomsn.pengajuan.approvals');
    Route::get('/gomsn/pengajuan/create', [PengajuanController::class, 'create'])->name('gomsn.pengajuan.create');
    Route::post('/gomsn/pengajuan', [PengajuanController::class, 'store'])->name('gomsn.pengajuan.store');
    Route::get('/gomsn/pengajuan/{pengajuan}', [PengajuanController::class, 'show'])->name('gomsn.pengajuan.show');
    Route::get('/gomsn/pengajuan/{pengajuan}/edit', [PengajuanController::class, 'edit'])->name('gomsn.pengajuan.edit');
    Route::put('/gomsn/pengajuan/{pengajuan}', [PengajuanController::class, 'update'])->name('gomsn.pengajuan.update');
    Route::post('/gomsn/pengajuan/{pengajuan}/submit', [PengajuanController::class, 'submit'])->name('gomsn.pengajuan.submit');
    Route::post('/gomsn/pengajuan/{pengajuan}/approve-atasan', [PengajuanController::class, 'approveAtasan'])->name('gomsn.pengajuan.approve-atasan');
    Route::post('/gomsn/pengajuan/{pengajuan}/approve-executive', [PengajuanController::class, 'approveExecutive'])->name('gomsn.pengajuan.approve-executive');
    Route::post('/gomsn/pengajuan/{pengajuan}/disburse', [PengajuanController::class, 'disburseFinance'])->name('gomsn.pengajuan.disburse');
    Route::post('/gomsn/pengajuan/{pengajuan}/realize', [PengajuanController::class, 'submitRealization'])->name('gomsn.pengajuan.realize');
    Route::post('/gomsn/pengajuan/{pengajuan}/reject', [PengajuanController::class, 'reject'])->name('gomsn.pengajuan.reject');

    // Stok & Inventaris Kantor (§8.1, Finance/Admin)
    Route::get('/gomsn/stok', [InventoryController::class, 'index'])->name('gomsn.inventory.index');
    Route::post('/gomsn/stok', [InventoryController::class, 'store'])->name('gomsn.inventory.store');
    Route::put('/gomsn/stok/{material}', [InventoryController::class, 'update'])->name('gomsn.inventory.update');
    Route::delete('/gomsn/stok/{material}', [InventoryController::class, 'destroy'])->name('gomsn.inventory.destroy');
    Route::post('/gomsn/stok/{material}/restock', [InventoryController::class, 'restock'])->name('gomsn.inventory.restock');
    Route::post('/gomsn/stok/{material}/adjust', [InventoryController::class, 'adjust'])->name('gomsn.inventory.adjust');
    Route::get('/gomsn/stok/{material}/movements', [InventoryController::class, 'movements'])->name('gomsn.inventory.movements');

    // Pengajuan Lembur (§8.2)
    Route::get('/gomsn/overtime', [OvertimeController::class, 'index'])->name('gomsn.overtime.index');
    Route::get('/gomsn/overtime/create', [OvertimeController::class, 'create'])->name('gomsn.overtime.create');
    Route::get('/gomsn/overtime/check-attendance', [OvertimeController::class, 'checkAttendance'])->name('gomsn.overtime.check-attendance');
    Route::post('/gomsn/overtime', [OvertimeController::class, 'store'])->name('gomsn.overtime.store');
    Route::put('/gomsn/overtime/{lembur}', [OvertimeController::class, 'update'])->name('gomsn.overtime.update');
    Route::post('/gomsn/overtime/{lembur}/approve', [OvertimeController::class, 'approve'])->name('gomsn.overtime.approve');
    Route::post('/gomsn/overtime/{lembur}/reject', [OvertimeController::class, 'reject'])->name('gomsn.overtime.reject');
    Route::post('/gomsn/overtime/{lembur}/pay', [OvertimeController::class, 'pay'])->name('gomsn.overtime.pay');

    // Penugasan Kerja / Tasks (§8.3)
    Route::get('/gomsn/tasks', [TaskController::class, 'index'])->name('gomsn.tasks.index');
    Route::post('/gomsn/tasks', [TaskController::class, 'store'])->name('gomsn.tasks.store');
    Route::post('/gomsn/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('gomsn.tasks.status');
    Route::post('/gomsn/tasks/subtasks/{subtask}/toggle', [TaskController::class, 'toggleSubtask'])->name('gomsn.tasks.subtasks.toggle');
    Route::post('/gomsn/tasks/{task}/comments', [TaskController::class, 'addComment'])->name('gomsn.tasks.comments.store');

    // Analitik MTTR & KPI Teknisi (§7.7, §11.1)
    Route::get('/gomsn/analytics/kpi', [KpiReportController::class, 'index'])->name('gomsn.analytics.kpi');
    Route::get('/gomsn/tickets/{tiket}/berita-acara', [KpiReportController::class, 'beritaAcara'])->name('gomsn.tickets.berita-acara');

    // Manajemen Jaringan: OLT, PON & ODP (NOC & Teknis)
    Route::get('/gomsn/network', [NetworkOdpController::class, 'index'])->name('gomsn.network.index');
    Route::post('/gomsn/network/olts/{olt}/pons', [NetworkOdpController::class, 'storePon'])->name('gomsn.network.pons.store');
    Route::put('/gomsn/network/pons/{pon}', [NetworkOdpController::class, 'updatePon'])->name('gomsn.network.pons.update');
    Route::delete('/gomsn/network/pons/{pon}', [NetworkOdpController::class, 'destroyPon'])->name('gomsn.network.pons.destroy');

    Route::get('/gomsn/network/olts/{olt}/pons/{pon}', [NetworkOdpController::class, 'showPon'])->name('gomsn.network.pons.show');
    Route::post('/gomsn/network/pons/{pon}/odps', [NetworkOdpController::class, 'storeOdp'])->name('gomsn.network.odps.store');
    Route::put('/gomsn/network/odps/{odp}', [NetworkOdpController::class, 'updateOdp'])->name('gomsn.network.odps.update');
    Route::delete('/gomsn/network/odps/{odp}', [NetworkOdpController::class, 'destroyOdp'])->name('gomsn.network.odps.destroy');

    Route::get('/gomsn/network/olts/{olt}/pons/{pon}/odps/{odp}', [NetworkOdpController::class, 'showOdp'])->name('gomsn.network.odps.show');
    Route::post('/gomsn/network/odps/{odp}/customers', [NetworkOdpController::class, 'storeCustomer'])->name('gomsn.network.customers.store');
    Route::put('/gomsn/network/customers/{customer}', [NetworkOdpController::class, 'updateCustomer'])->name('gomsn.network.customers.update');
    Route::delete('/gomsn/network/customers/{customer}', [NetworkOdpController::class, 'destroyCustomer'])->name('gomsn.network.customers.destroy');
    Route::get('/gomsn/network/odps/{odp}/histories', [NetworkOdpController::class, 'histories'])->name('gomsn.network.odps.histories');

    // Chatbot Asisten API (§5.8)
    Route::post('/api/chatbot/ask', [ChatbotController::class, 'ask'])->name('api.chatbot.ask');
});
