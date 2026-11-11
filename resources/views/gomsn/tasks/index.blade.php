<x-app-layout>
    @php
        $tab = $tab ?? 'assigned_to_me';
        $users = $users ?? collect();
    @endphp
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-heading font-bold text-[#1E3A8A] tracking-tight">
                    Penugasan Kerja (Task Management)
                </h1>
                <p class="text-sm text-slate-600 mt-1">
                    SOP §8.3: Koordinasi operasional harian, subtask checklist, dan tracking penyelesaian tugas teknisi &amp; staf.
                </p>
            </div>

            <button type="button" onclick="document.getElementById('modal-task').classList.remove('hidden')" class="btn-primary min-h-[44px] inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Beri Tugas Baru
            </button>
        </div>

        <!-- Tabs -->
        <div class="flex items-center gap-2 border-b border-slate-200 text-xs">
            <a href="{{ route('gomsn.tasks.index', ['tab' => 'assigned_to_me']) }}" 
               class="px-4 py-2.5 font-semibold border-b-2 transition {{ $tab === 'assigned_to_me' ? 'border-[#1E40AF] text-[#1E40AF]' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                Ditugaskan ke Saya
            </a>
            <a href="{{ route('gomsn.tasks.index', ['tab' => 'created_by_me']) }}" 
               class="px-4 py-2.5 font-semibold border-b-2 transition {{ $tab === 'created_by_me' ? 'border-[#1E40AF] text-[#1E40AF]' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                Diberikan oleh Saya
            </a>
            @if (auth()->user()->hasRole(['super_admin', 'atasan', 'manager_teknisi']))
            <a href="{{ route('gomsn.tasks.index', ['tab' => 'all']) }}" 
               class="px-4 py-2.5 font-semibold border-b-2 transition {{ $tab === 'all' ? 'border-[#1E40AF] text-[#1E40AF]' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                Semua Tugas Tim
            </a>
            @endif
        </div>

        <!-- Task Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($tasks as $task)
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between space-y-4 hover:shadow-md transition">
                <div class="space-y-3">
                    <!-- Priority & Status Badges -->
                    <div class="flex items-center justify-between gap-2">
                        @php
                            $prioBadge = match($task->priority) {
                                'urgent' => 'bg-red-100 text-red-800 border-red-200',
                                'high' => 'bg-orange-100 text-orange-800 border-orange-200',
                                'medium' => 'bg-blue-100 text-blue-800 border-blue-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200',
                            };
                            $statusBadge = match($task->status) {
                                'Completed' => 'bg-emerald-100 text-emerald-800',
                                'In Progress' => 'bg-blue-100 text-blue-800',
                                'Rejected' => 'bg-red-100 text-red-800',
                                default => 'bg-amber-100 text-amber-800',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border {{ $prioBadge }}">
                            {{ $task->priority }}
                        </span>

                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $statusBadge }}">
                            {{ $task->status }}
                        </span>
                    </div>

                    <!-- Title & Details -->
                    <div>
                        <h3 class="font-heading font-bold text-sm text-slate-900 leading-snug">
                            {{ $task->title }}
                        </h3>
                        @if ($task->description)
                        <p class="text-xs text-slate-600 mt-1 line-clamp-2">
                            {{ $task->description }}
                        </p>
                        @endif
                    </div>

                    <!-- Meta info -->
                    <div class="text-[11px] text-slate-500 space-y-1 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        <div class="flex items-center justify-between">
                            <span>Pemberi: <strong>{{ $task->assigner->name }}</strong></span>
                            @if ($task->due_date)
                            <span class="text-red-600 font-semibold">Due: {{ $task->due_date->format('d M') }}</span>
                            @endif
                        </div>
                        <div>
                            <span>Penerima: <strong>{{ $task->assignee->name }}</strong></span>
                        </div>
                    </div>

                    <!-- Subtasks Checklist -->
                    @if ($task->subtasks->isNotEmpty())
                    <div class="space-y-1.5 pt-1">
                        <span class="text-[11px] font-bold text-slate-700 block">Subtasks ({{ $task->subtasks->where('is_completed', true)->count() }}/{{ $task->subtasks->count() }}):</span>
                        <div class="space-y-1">
                            @foreach ($task->subtasks as $st)
                            <form method="POST" action="{{ route('gomsn.tasks.subtasks.toggle', $st->id) }}">
                                @csrf
                                <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                                    <input type="checkbox" onchange="this.form.submit()" {{ $st->is_completed ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500 w-3.5 h-3.5" />
                                    <span class="{{ $st->is_completed ? 'line-through text-slate-400' : '' }}">{{ $st->title }}</span>
                                </label>
                            </form>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Footer & Status Action -->
                <div class="pt-3 border-t border-slate-100 space-y-2">
                    <form method="POST" action="{{ route('gomsn.tasks.status', $task->id) }}" class="flex items-center gap-2">
                        @csrf
                        <select name="status" onchange="this.form.submit()" class="text-[11px] rounded-lg border-slate-300 py-1 flex-1">
                            <option value="Pending Approval" {{ $task->status === 'Pending Approval' ? 'selected' : '' }}>Pending Approval</option>
                            <option value="In Progress" {{ $task->status === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="Completed" {{ $task->status === 'Completed' ? 'selected' : '' }}>Completed</option>
                            @if (auth()->id() === $task->assigner_id || auth()->user()->hasRole(['super_admin', 'manager_teknisi']))
                            <option value="Rejected" {{ $task->status === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                            @endif
                        </select>
                    </form>
                </div>
            </div>
            @empty
            <div class="col-span-full py-12 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
                Tidak ada data penugasan pada tab ini.
            </div>
            @endforelse
        </div>

        @if ($tasks->hasPages())
        <div class="bg-white p-4 rounded-xl border border-slate-200">
            {{ $tasks->links() }}
        </div>
        @endif
    </div>

    <!-- Create Task Modal -->
    <div id="modal-task" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-heading font-bold text-base text-[#1E3A8A]">
                    Buat Penugasan Kerja Baru
                </h3>
                <button type="button" onclick="document.getElementById('modal-task').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg">
                    &times;
                </button>
            </div>

            <form method="POST" action="{{ route('gomsn.tasks.store') }}" class="space-y-4" x-data="{ subtasks: [''] }">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Penerima Tugas (Assignee) <span class="text-red-500">*</span></label>
                    <select name="assignee_id" required class="w-full text-xs rounded-xl border-slate-300 py-2">
                        <option value="">-- Pilih Rekan / Teknisi --</option>
                        @foreach ($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->employee_code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Penugasan <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: Pengecekan ODP Cluster Sakura Segmen 3" class="w-full text-xs rounded-xl border-slate-300 py-2" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Prioritas</label>
                        <select name="priority" class="w-full text-xs rounded-xl border-slate-300 py-2">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tenggat Waktu (Due Date)</label>
                        <input type="date" name="due_date" class="w-full text-xs rounded-xl border-slate-300 py-2" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi &amp; Instruksi Khusus</label>
                    <textarea name="description" rows="2" placeholder="Detail instruksi penugasan..." class="w-full text-xs rounded-xl border-slate-300 py-2"></textarea>
                </div>

                <!-- Subtask dynamic list -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-semibold text-slate-700">Checklist Subtasks</label>
                        <button type="button" @click="subtasks.push('')" class="text-xs text-blue-600 font-semibold hover:underline">
                            + Tambah Subtask
                        </button>
                    </div>
                    <template x-for="(st, idx) in subtasks" :key="idx">
                        <div class="flex items-center gap-2">
                            <input type="text" name="subtasks[]" placeholder="Langkah / checklist kerja..." class="flex-1 text-xs rounded-lg border-slate-300 py-1.5" />
                            <button type="button" @click="if (subtasks.length > 1) subtasks.splice(idx, 1)" class="text-red-500 text-sm font-bold">&times;</button>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modal-task').classList.add('hidden')" class="btn-secondary !px-4 !py-2 text-xs">
                        Batal
                    </button>
                    <button type="submit" class="btn-primary !px-6 !py-2 text-xs">
                        Simpan Penugasan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

