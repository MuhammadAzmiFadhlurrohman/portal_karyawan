@php
    $navUser = auth()->user();
    $userId = $navUser?->id ?? 0;
    $navCanApprovePengajuan = $navCanApprovePengajuan ?? false;
    $navCanApproveKoreksi = $navCanApproveKoreksi ?? false;
    $navPendingAtasanCount = $navPendingAtasanCount ?? 0;
    $navPendingKoreksiCount = $navPendingKoreksiCount ?? 0;
    $navPendingLemburCount = $navPendingLemburCount ?? 0;
    $navPendingLeaveHrdCount = $navPendingLeaveHrdCount ?? 0;
    $navAssignedTicketCount = $navAssignedTicketCount ?? 0;
    $isHrdRole = $isHrdRole ?? false;
    $navIsHrd = $navIsHrd ?? false;
    $navIsAtasan = $navIsAtasan ?? false;
    $canViewReports = $canViewReports ?? false;
    $canViewEmployeeRecap = $canViewEmployeeRecap ?? false;
    $isFinanceUser = $navUser && ($navUser->hasRole('finance') || strtolower($navUser->division ?? '') === 'finance');
@endphp

<!-- ============================================================== -->
<!-- MOBILE BOTTOM SHEET MODAL (DAPAT DIKUSTOMISASI OLEH SETIAP USER) -->
<!-- ============================================================== -->
<div x-show="bottomSheetOpen" 
     x-cloak 
     class="fixed inset-0 z-50 flex items-end justify-center md:hidden"
     x-data="mobileMenuCustomizer({{ $userId }})"
     @touchmove="if (isDragging) onDrag($event)"
     @touchend="if (isDragging) endDrag()"
     @mousemove.window="if (isDragging) onDrag($event)"
     @mouseup.window="if (isDragging) endDrag()">
    
    <!-- Backdrop Blur -->
    <div x-show="bottomSheetOpen"
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="if (!isEditMode) bottomSheetOpen = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"></div>

    <!-- Sliding Sheet Panel -->
    <div x-show="bottomSheetOpen"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         :style="isDragging ? 'transform: translateY(' + Math.max(0, sheetTranslateY) + 'px) !important; transition: none !important;' : (sheetTranslateY > 0 ? 'transform: translateY(0px); transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);' : '')"
         class="relative w-full max-w-xl mx-auto max-h-[88vh] bg-white rounded-t-[30px] shadow-[0_-16px_50px_rgba(0,0,0,0.35)] flex flex-col z-20 overflow-hidden">
        
        <!-- Texture Background -->
        <div class="bottom-sheet-bg-pattern absolute inset-0 pointer-events-none z-0" 
             style="background-color: #FFFFFF; background-image: radial-gradient(rgba(148, 163, 184, 0.14) 0.75px, transparent 0.75px); background-size: 20px 20px;"></div>

        <!-- Sheet Header Bar (Ocean Teal Gradient / Mengikuti Warna Tema Aplikasi) -->
        <div class="gomsn-sheet-header relative z-10 shrink-0 select-none overflow-hidden" 
             style="background: linear-gradient(135deg, #073337 0%, #0d5257 50%, #08383c 100%);">
            
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>

            <!-- Pull Handle Bar -->
            <div class="pt-2.5 pb-1 cursor-grab active:cursor-grabbing flex flex-col items-center justify-center group relative z-10"
                 @touchstart.passive="startDrag($event)"
                 @touchmove="onDrag($event)"
                 @touchend="endDrag()"
                 @mousedown="startDrag($event)"
                 @click="if (!isEditMode) bottomSheetOpen = false">
                <div class="w-11 h-1.5 bg-white/30 group-hover:bg-white/60 group-active:bg-[#5eead4] rounded-full transition-colors"></div>
            </div>

            <!-- Profile & Header Actions -->
            <div class="px-5 pt-2 pb-3.5 flex items-center justify-between gap-3 relative z-10">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="relative shrink-0">
                        @if ($navUser && $navUser->avatar_url)
                            <img src="{{ $navUser->avatar_url }}" 
                                 alt="" 
                                 class="h-11 w-11 rounded-2xl object-cover shadow-md ring-2 ring-white/25 shrink-0">
                        @else
                            <div class="h-11 w-11 rounded-2xl bg-gradient-to-tr from-[#18C4C4] to-[#0D9488] text-white flex items-center justify-center font-bold text-sm shadow-md ring-2 ring-white/25 shrink-0">
                                {{ $navUser?->initials ?? 'MSN' }}
                            </div>
                        @endif
                        <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-emerald-400 ring-2 ring-[#073337]"></span>
                    </div>

                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <h3 class="font-extrabold text-[14px] text-white tracking-tight leading-snug truncate">Menu Layanan</h3>
                        </div>
                        <div class="flex items-center gap-1.5 mt-1 min-w-0">
                            <span class="text-xs font-semibold text-teal-50/95 truncate">{{ $navUser?->name ?? 'User' }}</span>
                            <span class="text-teal-300/50 text-xs">•</span>
                            <span class="text-[10px] font-bold uppercase tracking-wide text-teal-100 bg-white/12 px-2 py-0.5 rounded-md border border-white/15 shrink-0">
                                {{ $navUser?->division ?: ($navUser?->getRoleNames()?->first() ? ucfirst($navUser->getRoleNames()->first()) : 'MSN') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right Header Actions: Customize Toggle + Close -->
                <div class="flex items-center gap-2 shrink-0">
                    <!-- Tombol Mode Kustomisasi / Atur Menu -->
                    <button type="button" 
                            @click="isEditMode = !isEditMode"
                            :class="isEditMode ? 'bg-[#5eead4] text-slate-900 font-extrabold shadow-md scale-105' : 'bg-white/10 hover:bg-white/20 text-white/90 hover:text-white border border-white/15'"
                            class="px-2.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-2xs"
                            title="Atur Urutan, Nama & Warna Menu">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                        <span x-text="isEditMode ? 'Selesai' : 'Atur Menu'"></span>
                    </button>

                    <!-- Close Button -->
                    <button type="button" 
                            @click.stop="bottomSheetOpen = false" 
                            aria-label="Tutup Menu"
                            class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-white/90 hover:text-white flex items-center justify-center transition active:scale-95 cursor-pointer shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Search Bar (Hanya tampil di luar Edit Mode) -->
            <div x-show="!isEditMode" class="px-5 pb-3.5 relative z-10">
                <div class="relative flex items-center">
                    <input type="text" 
                           x-model="sheetSearch" 
                           placeholder="Cari layanan (absen, lembur, kasbon, tiket)..."
                           class="peer w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-white/20 bg-white/12 focus:bg-white focus:border-white focus:ring-2 focus:ring-[#5eead4]/40 transition text-white focus:text-slate-800 placeholder:text-teal-100/70 focus:placeholder:text-slate-400">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none z-10 text-teal-100 peer-focus:text-[#155E63] transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <button type="button" 
                            x-show="sheetSearch" 
                            @click="sheetSearch = ''" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center z-10 text-teal-200 hover:text-white peer-focus:text-slate-400 peer-focus:hover:text-slate-700 text-xs cursor-pointer">
                        &times;
                    </button>
                </div>
            </div>

            <!-- Banner Kustomisasi Menu Aktif -->
            <div x-show="isEditMode" 
                 x-transition
                 class="px-5 pb-3 pt-2 text-xs text-white/90 border-t border-white/10 flex items-center justify-between gap-2.5">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-[#5eead4] shrink-0 animate-pulse"></span>
                    <span class="text-[11px] leading-snug text-teal-100"><b class="text-white">Tarik &amp; geser</b> kartu untuk ubah urutan, atau ketuk <b class="text-white">Ubah</b> untuk ganti nama &amp; warna.</span>
                </div>
                <button type="button" 
                        @click="resetCustomizations()"
                        style="background: rgba(244, 63, 94, 0.22); border: 1px solid rgba(251, 113, 133, 0.45); color: #fecdd3;"
                        class="px-2.5 py-1.5 text-[10px] font-bold rounded-lg shrink-0 transition active:scale-95 cursor-pointer">
                    Reset
                </button>
            </div>
        </div>

        <!-- Scrollable Content of Menus -->
        <div x-ref="sheetScrollArea" class="p-4 sm:p-5 overflow-y-auto space-y-5 flex-1 max-h-[calc(88vh-140px)] relative z-10">
            
            <!-- Loop Per Kategori Menu -->
            <template x-for="(cat, catIndex) in categories" :key="cat.key">
                <div x-show="cat.visible && (isEditMode || getCategoryItems(cat.key).length > 0) && (!sheetSearch || isCategoryMatch(cat))"
                     :data-drop-cat="cat.key"
                     class="space-y-2.5 rounded-2xl transition-colors"
                     :style="draggingItem && draggingItem.catKey !== cat.key ? 'padding: 6px; margin: -6px; background: rgba(240, 253, 250, 0.55);' : ''">
                    
                    <!-- Kategori Header (Bisa Di-Edit di Mode Atur Menu) -->
                    <div class="flex items-center justify-between gap-2 pt-0.5">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-1.5 h-3.5 rounded-full shrink-0"
                                  :class="!hasCustomCategoryColor(cat) ? cat.indicatorClass : ''"
                                  :style="hasCustomCategoryColor(cat) ? getCategoryIndicatorStyle(cat) : ''"></span>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-700 truncate"
                                  x-text="getCategoryTitle(cat)"></span>
                        </div>

                        <!-- Normal Mode: Subtitle Kanan -->
                        <span x-show="!isEditMode" class="text-[10px] text-slate-400 font-medium shrink-0" x-text="getCategorySubtitle(cat)"></span>

                        <!-- Edit Mode: Tombol Edit Judul sejajar 1 baris rapi di kanan -->
                        <button type="button"
                                x-show="isEditMode"
                                @click.stop="openCategoryEditModal(cat)"
                                class="inline-flex items-center gap-1 shrink-0 whitespace-nowrap cursor-pointer transition active:scale-95"
                                style="height: 24px; padding: 0 9px; border-radius: 999px; background: #f0fdfa; color: #0f766e; border: 1px solid #99f6e4; font-size: 10px; font-weight: 700;"
                                title="Ubah Judul Kategori">
                            <svg style="width: 10px; height: 10px; flex-shrink: 0;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            <span>Edit Judul</span>
                        </button>
                    </div>

                    <!-- Dropzone Kosong jika semua menu di grup ini dipindahkan -->
                    <div x-show="isEditMode && getCategoryItems(cat.key).length === 0"
                         :data-drop-cat="cat.key"
                         style="border: 1.5px dashed #94a3b8; border-radius: 14px; padding: 14px 12px; text-align: center; font-size: 11px; color: #64748b; background: #f8fafc;">
                        Tarik &amp; letakkan kartu menu ke sini
                    </div>

                    <!-- Grid Item Menu -->
                    <div class="grid grid-cols-2 gap-2.5" :data-drop-cat="cat.key">
                        <template x-for="(item, itemIndex) in getCategoryItems(cat.key)" :key="item.id">
                            <div x-show="item.visible && (!sheetSearch || isItemMatch(item))"
                                 :data-menu-item-id="item.id"
                                 :data-menu-cat="cat.key"
                                 @pointerdown="if (isEditMode) handleCardPointerDown($event, cat.key, item, $el)"
                                 class="relative group rounded-2xl transition-all duration-150 flex flex-col overflow-hidden bg-white shadow-2xs select-none"
                                 :style="getCardStyle(item)">
                                
                                <!-- Area Kartu Utama -->
                                <a :href="isEditMode ? 'javascript:void(0)' : item.url" 
                                   @click="if (isEditMode) { $event.preventDefault(); if (!justDragged) openEditModal(item); }"
                                   class="p-3 flex items-center justify-between gap-2 transition-all flex-1"
                                   :style="isEditMode ? 'cursor: grab;' : ''"
                                   draggable="false"
                                   :title="isEditMode ? 'Tahan & tarik untuk geser urutan, atau ketuk untuk ubah' : getItemTitle(item)">
                                    
                                    <div class="flex items-center gap-2.5 min-w-0 pointer-events-none">
                                        <!-- Icon Container dengan Warna Kustom (Inline Style) -->
                                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-colors shadow-2xs"
                                             :style="getIconBoxStyle(item)">
                                            <div x-html="item.iconSvg"></div>
                                        </div>

                                        <!-- Text Title & Subtitle Kustom -->
                                        <div class="min-w-0">
                                            <span class="font-bold text-xs text-slate-800 block truncate leading-snug"
                                                  x-text="getItemTitle(item)"></span>
                                            <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight"
                                                  x-text="getItemSubtitle(item)"></span>
                                        </div>
                                    </div>

                                    <!-- Badge Count (jika ada) -->
                                    <template x-if="item.badge && item.badge > 0 && !isEditMode">
                                        <span class="w-5 h-5 rounded-full text-[10px] font-bold bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-2xs"
                                              x-text="item.badge"></span>
                                    </template>
                                </a>

                                <!-- Bar Kontrol Edit: Grip Tarik Geser + Tombol Ubah -->
                                <div x-show="isEditMode" 
                                     class="bg-slate-50 border-t border-slate-200"
                                     style="padding: 5px 6px;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px;">
                                        <!-- Handle Tarik Geser (touch-action: none untuk drag instan tanpa bentrok scroll) -->
                                        <div @pointerdown.stop.prevent="beginItemDrag($event, cat.key, item, $el.closest('[data-menu-item-id]'))"
                                             style="flex: 1; height: 26px; border-radius: 8px; background: #e2e8f0; color: #334155; display: inline-flex; align-items: center; justify-content: center; gap: 4px; font-size: 10px; font-weight: 700; cursor: grab; touch-action: none; user-select: none; -webkit-user-select: none; padding: 0 6px;"
                                             title="Tarik untuk geser posisi">
                                            <svg style="width: 12px; height: 12px; flex-shrink: 0; color: #475569;" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M7 4a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm7-12a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
                                            </svg>
                                            <span>Geser</span>
                                        </div>

                                        <!-- Tombol Edit Nama & Warna -->
                                        <button type="button" 
                                                @pointerdown.stop
                                                @click.stop="openEditModal(item)"
                                                style="height: 26px; border-radius: 8px; background: #0d9488; color: #ffffff; display: inline-flex; align-items: center; justify-content: center; gap: 4px; font-size: 10px; font-weight: 700; border: none; cursor: pointer; padding: 0 9px; flex-shrink: 0;"
                                                title="Ganti Nama & Warna">
                                            <svg style="width: 11px; height: 11px; flex-shrink: 0;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            <span>Ubah</span>
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </template>
                    </div>

                </div>
            </template>

            <!-- Tombol Logout Permanen di Bawah -->
            <div x-show="!sheetSearch || 'logout keluar sesi'.includes(sheetSearch.toLowerCase())" class="pt-2">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full p-3 rounded-2xl border border-rose-200/90 bg-white hover:bg-rose-50/80 hover:border-rose-300 hover:shadow-sm active:scale-[0.98] transition-all flex items-center gap-2.5 group shadow-2xs text-left cursor-pointer">
                        <div class="w-9 h-9 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 group-hover:bg-rose-600 group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="font-bold text-xs text-rose-600 block truncate leading-snug">Logout / Keluar</span>
                            <span class="text-[10px] text-rose-400 block truncate mt-0.5 leading-tight">Akhiri Sesi Portal</span>
                        </div>
                    </button>
                </form>
            </div>

        </div>
    </div>

    <!-- ============================================================== -->
    <!-- FLOATING DRAG GHOST CARD (GPU 60FPS MENGIKUTI JARI)            -->
    <!-- ============================================================== -->
    <template x-if="draggingItem">
        <div x-ref="dragGhost"
             class="rounded-2xl bg-white overflow-hidden"
             :style="`position: fixed; top: 0; left: 0; z-index: 9990; pointer-events: none; width: ${dragPos.width}px; will-change: transform; transform: translate3d(${dragPos.x}px, ${dragPos.y}px, 0) scale(1.04) rotate(-0.8deg); box-shadow: 0 22px 44px rgba(15, 23, 42, 0.24), 0 4px 12px rgba(13, 148, 136, 0.16); border: 2px solid #0d9488; opacity: 0.98;`">
            <div class="p-3 flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                     :style="getIconBoxStyle(draggingItem.item)">
                    <div x-html="draggingItem.item.iconSvg"></div>
                </div>
                <div class="min-w-0">
                    <span class="font-bold text-xs text-slate-900 block truncate leading-snug"
                          x-text="getItemTitle(draggingItem.item)"></span>
                    <span class="text-[10px] text-slate-500 block truncate mt-0.5 leading-tight"
                          x-text="getItemSubtitle(draggingItem.item)"></span>
                </div>
            </div>
        </div>
    </template>

    <!-- ============================================================== -->
    <!-- MODAL EDIT NAMA & WARNA MENU (Z-INDEX TINGGI DI ATAS SHEET)    -->
    <!-- ============================================================== -->
    <div x-show="editingItem !== null" 
         x-cloak 
         class="fixed inset-0 flex items-center justify-center p-4"
         style="z-index: 9999;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="fixed inset-0" style="background: rgba(15, 23, 42, 0.72); backdrop-filter: blur(4px);" @click="editingItem = null"></div>

        <div class="relative w-full max-w-sm bg-white rounded-3xl shadow-2xl border border-slate-100 p-5 overflow-y-auto"
             style="position: relative; z-index: 10; max-height: 90vh;"
             @click.stop>
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center font-bold text-xs border border-teal-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm text-slate-900 leading-tight">Kustomisasi Menu</h4>
                        <p class="text-[10px] text-slate-400 mt-0.5">Ubah nama dan warna sesuai seleramu</p>
                    </div>
                </div>
                <button type="button" @click="editingItem = null" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center text-sm font-bold">
                    &times;
                </button>
            </div>

            <!-- Modal Form -->
            <div class="space-y-4 pt-4">
                
                <!-- Input Nama / Judul -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Nama Menu (Judul)
                    </label>
                    <input type="text" 
                           x-model="editForm.title" 
                           placeholder="Contoh: Absen Pagi"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500 font-semibold text-slate-800">
                </div>

                <!-- Input Sub-Keterangan -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Sub-Keterangan (Keterangan Singkat)
                    </label>
                    <input type="text" 
                           x-model="editForm.subtitle" 
                           placeholder="Contoh: Scan Wajah & GPS"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500 text-slate-600">
                </div>

                <!-- Pilihan Warna (Color Palette) -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Pilih Warna Tema Kartu
                    </label>
                    <div class="grid grid-cols-5 gap-2.5">
                        <template x-for="c in colorPalette" :key="c.name">
                            <button type="button" 
                                    @click="editForm.color = c.name"
                                    :title="c.label"
                                    class="h-10 rounded-xl flex items-center justify-center transition-all relative cursor-pointer"
                                    :class="editForm.color === c.name ? 'ring-2 ring-offset-2 ring-slate-800 scale-105 shadow-md' : 'opacity-85 hover:opacity-100 hover:scale-100 shadow-2xs'"
                                    :style="'background: ' + c.previewHex">
                                <template x-if="editForm.color === c.name">
                                    <svg class="w-4 h-4 text-white drop-shadow-xs" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </template>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Preview Kartu Real-time -->
                <div class="pt-1">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                        Pratinjau Tampilan
                    </label>
                    <div class="p-3 rounded-2xl border bg-white flex items-center gap-2.5 shadow-2xs"
                         :style="'border-color: ' + getPaletteObj(editForm.color).border + '; background: linear-gradient(135deg, #ffffff 55%, ' + getPaletteObj(editForm.color).bg + ' 100%);'">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 shadow-2xs"
                             :style="'background: ' + getPaletteObj(editForm.color).bg + '; color: ' + getPaletteObj(editForm.color).previewHex + '; border: 1px solid ' + getPaletteObj(editForm.color).border + ';'">
                            <div x-html="editingItem ? editingItem.iconSvg : ''"></div>
                        </div>
                        <div class="min-w-0">
                            <span class="font-bold text-xs text-slate-900 block truncate leading-snug" x-text="editForm.title || 'Nama Menu'"></span>
                            <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight" x-text="editForm.subtitle || 'Sub-keterangan'"></span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2 pt-2">
                    <button type="button" 
                            @click="editingItem = null"
                            class="flex-1 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="button" 
                            @click="saveItemEdit()"
                            style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%) !important;"
                            class="flex-1 py-2.5 rounded-xl text-white text-xs font-bold shadow-md hover:brightness-105 active:scale-95 transition">
                        Simpan
                    </button>
                </div>

            </div>

        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL EDIT JUDUL KATEGORI / GRUP MENU (Z-INDEX 9999)          -->
    <!-- ============================================================== -->
    <div x-show="editingCategory !== null"
         x-cloak
         class="fixed inset-0 flex items-center justify-center p-4"
         style="z-index: 9999;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="fixed inset-0" style="background: rgba(15, 23, 42, 0.72); backdrop-filter: blur(4px);" @click="editingCategory = null"></div>

        <div class="relative w-full max-w-sm bg-white rounded-3xl shadow-2xl border border-slate-100 p-5 overflow-y-auto"
             style="position: relative; z-index: 10; max-height: 90vh;"
             @click.stop>

            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center font-bold text-xs border border-teal-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10M4 18h7"/></svg>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm text-slate-900 leading-tight">Edit Judul Grup</h4>
                        <p class="text-[10px] text-slate-400 mt-0.5">Ubah nama bagian &amp; label sesuai keinginan</p>
                    </div>
                </div>
                <button type="button" @click="editingCategory = null" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center text-sm font-bold">
                    &times;
                </button>
            </div>

            <!-- Modal Form -->
            <div class="space-y-4 pt-4">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Judul Grup Menu
                    </label>
                    <input type="text"
                           x-model="categoryForm.title"
                           placeholder="Contoh: Menu Favorit"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500 font-semibold text-slate-800">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Label Kanan (Opsional)
                    </label>
                    <input type="text"
                           x-model="categoryForm.subtitle"
                           placeholder="Contoh: Akses Cepat"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500 text-slate-600">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Warna Garis Indikator
                    </label>
                    <div class="grid grid-cols-5 gap-2.5">
                        <template x-for="c in colorPalette" :key="c.name">
                            <button type="button"
                                    @click="categoryForm.color = c.name"
                                    :title="c.label"
                                    class="h-9 rounded-xl flex items-center justify-center transition-all relative cursor-pointer"
                                    :class="categoryForm.color === c.name ? 'ring-2 ring-offset-2 ring-slate-800 scale-105 shadow-md' : 'opacity-85 hover:opacity-100 shadow-2xs'"
                                    :style="'background: ' + c.previewHex">
                                <template x-if="categoryForm.color === c.name">
                                    <svg class="w-4 h-4 text-white drop-shadow-xs" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </template>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2 pt-2">
                    <button type="button"
                            @click="editingCategory = null"
                            class="flex-1 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="button"
                            @click="saveCategoryEdit()"
                            style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%) !important;"
                            class="flex-1 py-2.5 rounded-xl text-white text-xs font-bold shadow-md hover:brightness-105 active:scale-95 transition">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ============================================================== -->
<!-- ALPINE.JS LOGIC FOR CUSTOMIZABLE MOBILE MENU                   -->
<!-- ============================================================== -->
<script>
function mobileMenuCustomizer(userId) {
    return {
        sheetSearch: '',
        isDragging: false,
        dragStartY: 0,
        sheetTranslateY: 0,
        isEditMode: false,
        editingItem: null,
        editingCategory: null,
        draggingItem: null,
        isDropping: false,
        dragPos: { x: 0, y: 0, width: 160, offsetX: 0, offsetY: 0 },
        lastPointer: { x: 0, y: 0 },
        dragRafId: null,
        lastSwapAt: 0,
        holdTimer: null,
        pendingDrag: null,
        justDragged: false,
        storageKey: 'gomsn_mobile_menu_user_' + userId,
        customizations: {},
        editForm: {
            id: '',
            title: '',
            subtitle: '',
            color: 'teal'
        },
        categoryForm: {
            key: '',
            title: '',
            subtitle: '',
            color: ''
        },

        colorPalette: [
            { name: 'teal', label: 'Teal GoMSN', previewHex: '#0d9488', bg: '#f0fdfa', border: '#99f6e4' },
            { name: 'emerald', label: 'Emerald Hijau', previewHex: '#10b981', bg: '#ecfdf5', border: '#a7f3d0' },
            { name: 'cyan', label: 'Cyan Biru Laut', previewHex: '#06b6d4', bg: '#ecfeff', border: '#a5f3fc' },
            { name: 'blue', label: 'Biru Royal', previewHex: '#2563eb', bg: '#eff6ff', border: '#bfdbfe' },
            { name: 'indigo', label: 'Indigo / Navy', previewHex: '#6366f1', bg: '#eef2ff', border: '#c7d2fe' },
            { name: 'purple', label: 'Ungu Cerah', previewHex: '#a855f7', bg: '#faf5ff', border: '#e9d5ff' },
            { name: 'rose', label: 'Rose / Merah', previewHex: '#f43f5e', bg: '#fff1f2', border: '#fecdd3' },
            { name: 'amber', label: 'Amber / Emas', previewHex: '#d97706', bg: '#fffbeb', border: '#fde68a' },
            { name: 'orange', label: 'Oranye Terang', previewHex: '#ea580c', bg: '#fff7ed', border: '#fed7aa' },
            { name: 'slate', label: 'Slate Elegan', previewHex: '#475569', bg: '#f1f5f9', border: '#cbd5e1' },
        ],

        categories: [
            {
                key: 'pengajuan',
                title: 'Pengajuan & Operasional',
                subtitle: 'Layanan',
                indicatorClass: 'bg-gradient-to-b from-amber-500 to-orange-500',
                visible: true,
                items: [
                    {
                        id: 'pengajuan_saya',
                        defaultTitle: 'Pengajuan Saya',
                        defaultSubtitle: 'Kasbon, Beli, Stok',
                        defaultColor: 'teal',
                        url: '{{ route('gomsn.pengajuan.index') }}',
                        visible: true,
                        badge: null,
                        keywords: 'pengajuan kasbon dana beli barang stok',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>'
                    },
                    @if ($navCanApprovePengajuan)
                    {
                        id: 'pengajuan_approvals',
                        defaultTitle: 'Persetujuan',
                        defaultSubtitle: 'Tim Karyawan',
                        defaultColor: 'amber',
                        url: '{{ route('gomsn.pengajuan.approvals') }}',
                        visible: true,
                        badge: {{ $navPendingAtasanCount > 0 ? $navPendingAtasanCount : 'null' }},
                        keywords: 'persetujuan approval atasan kasbon',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                    },
                    @endif
                    @if ($navCanApproveKoreksi)
                    {
                        id: 'attendance_corrections',
                        defaultTitle: 'Pengajuan Absen',
                        defaultSubtitle: 'Koreksi Kehadiran',
                        defaultColor: 'emerald',
                        url: '{{ route('attendance.corrections.approvals') }}',
                        visible: true,
                        badge: {{ $navPendingKoreksiCount > 0 ? $navPendingKoreksiCount : 'null' }},
                        keywords: 'pengajuan absen koreksi kehadiran atasan',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>'
                    },
                    @endif
                    {
                        id: 'lembur_saya',
                        defaultTitle: '{{ $isHrdRole ? "Lembur Saya" : "Lembur" }}',
                        defaultSubtitle: 'Pengajuan & SPK',
                        defaultColor: 'blue',
                        url: '{{ $isHrdRole ? route('gomsn.overtime.index', ['view_mode' => 'saya']) : route('gomsn.overtime.index') }}',
                        visible: true,
                        badge: {{ (!$isHrdRole && ($navPendingLemburCount ?? 0) > 0) ? $navPendingLemburCount : 'null' }},
                        keywords: 'lembur overtime spk saya jam kerja',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                    },
                    @if ($isFinanceUser)
                    {
                        id: 'stok_barang_ops',
                        defaultTitle: 'Stok Barang',
                        defaultSubtitle: 'Inventaris Gudang',
                        defaultColor: 'emerald',
                        url: '{{ route('gomsn.inventory.index') }}',
                        visible: true,
                        badge: null,
                        keywords: 'stok barang inventaris material gudang',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>'
                    },
                    @endif
                ]
            },
            {
                key: 'absensi',
                title: 'Absensi & Cuti Saya',
                subtitle: 'Pribadi',
                indicatorClass: 'bg-gradient-to-b from-teal-500 to-cyan-500',
                visible: true,
                items: [
                    {
                        id: 'absen',
                        defaultTitle: 'Absen',
                        defaultSubtitle: 'Biometrik & GPS',
                        defaultColor: 'teal',
                        url: '{{ route('attendance.index') }}',
                        visible: true,
                        badge: null,
                        keywords: 'absen presensi masuk pulang selfie wajah kamera gps',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>'
                    },
                    {
                        id: 'riwayat_absen',
                        defaultTitle: 'Riwayat Absen Saya',
                        defaultSubtitle: 'Log Kehadiran',
                        defaultColor: 'cyan',
                        url: '{{ route('attendance.history') }}',
                        visible: true,
                        badge: null,
                        keywords: 'riwayat absen log kehadiran rekap histori',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                    },
                    {
                        id: 'jadwal_kerja',
                        defaultTitle: 'Jadwal Kerja Saya',
                        defaultSubtitle: 'Kalender Shift',
                        defaultColor: 'indigo',
                        url: '{{ route('attendance.schedules') }}',
                        visible: true,
                        badge: null,
                        keywords: 'jadwal kerja shift kalender roster tukar shift',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
                    },
                    {
                        id: 'cuti_izin',
                        defaultTitle: 'Cuti & Izin Saya',
                        defaultSubtitle: 'Saldo & Pengajuan',
                        defaultColor: 'emerald',
                        url: '{{ route('attendance.leaves') }}',
                        visible: true,
                        badge: null,
                        keywords: 'cuti izin sakit saldo pengajuan libur',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'
                    },
                    {
                        id: 'potongan',
                        defaultTitle: 'Potongan Saya',
                        defaultSubtitle: 'Potongan & Denda',
                        defaultColor: 'amber',
                        url: '{{ route('attendance.deductions') }}',
                        visible: true,
                        badge: null,
                        keywords: 'potongan denda gaji terlambat telat absen keuangan',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                    },
                    @if (! $navIsHrd)
                    {
                        id: 'leaderboard_user',
                        defaultTitle: 'Leaderboard',
                        defaultSubtitle: 'Peringkat Disiplin',
                        defaultColor: 'teal',
                        url: '{{ route('attendance.leaderboard') }}',
                        visible: true,
                        badge: null,
                        keywords: 'leaderboard peringkat ranking disiplin absen rajin',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>'
                    },
                    @endif
                    @if (! $navIsHrd && $canViewEmployeeRecap)
                    {
                        id: 'absen_karyawan_admin',
                        defaultTitle: 'Absen Karyawan',
                        defaultSubtitle: 'Rekap & Potongan',
                        defaultColor: 'teal',
                        url: '{{ route('attendance.employee-recap') }}',
                        visible: true,
                        badge: null,
                        keywords: 'absen karyawan rekap admin',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'
                    },
                    @endif
                ]
            },
            @if ($navIsHrd)
            {
                key: 'kelola_karyawan',
                title: 'Kelola Karyawan',
                subtitle: 'Khusus HRD',
                indicatorClass: 'bg-gradient-to-b from-teal-500 to-emerald-500',
                visible: true,
                items: [
                    {
                        id: 'data_karyawan_hrd',
                        defaultTitle: 'Data Karyawan',
                        defaultSubtitle: 'Profil & Kontak',
                        defaultColor: 'teal',
                        url: '{{ route('portal.employees.index') }}',
                        visible: true,
                        badge: null,
                        keywords: 'data karyawan sdm staf staff profil kontak pegawai',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>'
                    },
                    {
                        id: 'absen_karyawan_hrd',
                        defaultTitle: 'Absen Karyawan',
                        defaultSubtitle: 'Rekap & Potongan',
                        defaultColor: 'teal',
                        url: '{{ route('attendance.employee-recap') }}',
                        visible: true,
                        badge: null,
                        keywords: 'absen karyawan rekap kehadiran potongan staf hrd',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'
                    },
                    {
                        id: 'pengajuan_absen_hrd',
                        defaultTitle: 'Pengajuan Absen',
                        defaultSubtitle: 'Persetujuan Cuti & Izin',
                        defaultColor: 'teal',
                        url: '{{ route('attendance.leaves.approvals') }}',
                        visible: true,
                        badge: {{ ($navPendingLeaveHrdCount ?? 0) > 0 ? $navPendingLeaveHrdCount : 'null' }},
                        keywords: 'pengajuan absen cuti izin hrd approval persetujuan',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>'
                    },
                    {
                        id: 'pengajuan_mobil_hrd',
                        defaultTitle: 'Pengajuan Mobil',
                        defaultSubtitle: 'Pemantauan Kendaraan',
                        defaultColor: 'teal',
                        url: '{{ route('gomsn.car-usage.index') }}',
                        visible: true,
                        badge: null,
                        keywords: 'pengajuan mobil kendaraan bensin operasional hrd',
                        iconSvg: '<svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 3.1C2.1 11.4 2 11.7 2 12.1V16c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>'
                    },
                    {
                        id: 'lembur_karyawan_hrd',
                        defaultTitle: 'Lembur Karyawan',
                        defaultSubtitle: 'Pengajuan & SPK',
                        defaultColor: 'teal',
                        url: '{{ route('gomsn.overtime.index', ['view_mode' => 'karyawan']) }}',
                        visible: true,
                        badge: {{ ($navPendingLemburCount ?? 0) > 0 ? $navPendingLemburCount : 'null' }},
                        keywords: 'lembur karyawan spk hrd approval persetujuan',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                    },
                    {
                        id: 'leaderboard_hrd',
                        defaultTitle: 'Leaderboard',
                        defaultSubtitle: 'Peringkat Disiplin',
                        defaultColor: 'teal',
                        url: '{{ route('attendance.leaderboard') }}',
                        visible: true,
                        badge: null,
                        keywords: 'leaderboard hrd disiplin ranking peringkat',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>'
                    },
                ]
            },
            @endif
            {
                key: 'pekerjaan',
                title: 'Pekerjaan & Lapangan',
                subtitle: 'Operasional',
                indicatorClass: 'bg-gradient-to-b from-rose-500 to-pink-500',
                visible: true,
                items: [
                    {
                        id: 'network',
                        defaultTitle: 'OLT, PON & ODP',
                        defaultSubtitle: 'Jaringan & Pelanggan',
                        defaultColor: 'teal',
                        url: '{{ route('gomsn.network.index') }}',
                        visible: true,
                        badge: null,
                        keywords: 'olt pon odp jaringan pelanggan port fiber ftth',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>'
                    },
                    {
                        id: 'coverage',
                        defaultTitle: 'Cek Coverage',
                        defaultSubtitle: 'Presisi Jarak FTTH',
                        defaultColor: 'cyan',
                        url: '{{ route('gomsn.network.coverage') }}',
                        visible: true,
                        badge: null,
                        keywords: 'cek coverage peta maps radius odp terdekat ftth rute jalan',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'
                    },
                    {
                        id: 'tickets',
                        defaultTitle: 'Tiket Gangguan',
                        defaultSubtitle: 'Laporan NOC / SPK',
                        defaultColor: 'rose',
                        url: '{{ route('tickets.index') }}',
                        visible: true,
                        badge: {{ ($navAssignedTicketCount ?? 0) > 0 ? $navAssignedTicketCount : 'null' }},
                        keywords: 'tiket gangguan noc spk laporan kendala internet',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>'
                    },
                ]
            },
            {
                key: 'lainnya',
                title: 'Lainnya',
                subtitle: 'Sistem & Pengaturan',
                indicatorClass: 'bg-gradient-to-b from-slate-600 to-slate-400',
                visible: true,
                items: [
                    {
                        id: 'kpi',
                        defaultTitle: 'Laporan KPI',
                        defaultSubtitle: 'Analitik Performa',
                        defaultColor: 'indigo',
                        url: '{{ route('gomsn.analytics.kpi') }}',
                        visible: true,
                        badge: null,
                        keywords: 'kpi analitik performa laporan kerja teknisi',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
                    },
                    {
                        id: 'inventory_lainnya',
                        defaultTitle: 'Stok Barang',
                        defaultSubtitle: 'Stok Gudang',
                        defaultColor: 'emerald',
                        url: '{{ route('gomsn.inventory.index') }}',
                        visible: true,
                        badge: null,
                        keywords: 'stok barang gudang inventaris material kabel ont',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>'
                    },
                    {
                        id: 'profile',
                        defaultTitle: 'Profil Saya',
                        defaultSubtitle: 'Kontak & Rekening',
                        defaultColor: 'teal',
                        url: '{{ route('portal.profile') }}',
                        visible: true,
                        badge: null,
                        keywords: 'profil saya akun rekening kontak hp email password',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'
                    },
                    {
                        id: 'settings',
                        defaultTitle: 'Pengaturan',
                        defaultSubtitle: '{{ (auth()->user()?->can("portal.settings.manage") || auth()->user()?->hasRole(["super_admin", "admin"])) ? "Konfigurasi Aplikasi" : "Tema & Notifikasi" }}',
                        defaultColor: 'slate',
                        url: '{{ route('portal.settings') }}',
                        visible: true,
                        badge: null,
                        keywords: 'pengaturan konfigurasi setting tema gelap terang notifikasi preferensi',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'
                    },
                    @hasrole(['admin', 'super_admin'])
                    {
                        id: 'sessions',
                        defaultTitle: 'Perangkat Saya',
                        defaultSubtitle: 'Binding Multi-Device',
                        defaultColor: 'slate',
                        url: '{{ route('portal.sessions') }}',
                        visible: true,
                        badge: null,
                        keywords: 'perangkat device hp login sesi binding multi-device',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>'
                    },
                    @endhasrole
                    @can('portal.audit.view')
                    {
                        id: 'audit',
                        defaultTitle: 'Audit Log',
                        defaultSubtitle: 'Riwayat Keamanan',
                        defaultColor: 'slate',
                        url: '{{ route('portal.audit') }}',
                        visible: true,
                        badge: null,
                        keywords: 'audit log riwayat aktivitas keamanan login',
                        iconSvg: '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
                    },
                    @endcan
                ]
            }
        ],

        init() {
            this.loadCustomizations();
            this.ensureLayout();

            window.addEventListener('pointermove', (e) => this.onItemPointerMove(e), { passive: false });
            window.addEventListener('pointerup', () => this.endItemDrag());
            window.addEventListener('pointercancel', () => this.endItemDrag());
            window.addEventListener('touchmove', (e) => {
                if (this.draggingItem) {
                    e.preventDefault();
                }
            }, { passive: false });
        },

        getAllItemsMap() {
            const map = {};
            for (const cat of this.categories) {
                for (const item of cat.items) {
                    map[item.id] = item;
                }
            }
            return map;
        },

        ensureLayout() {
            const allMap = this.getAllItemsMap();
            const assigned = new Set();

            if (!this.customizations.layout || typeof this.customizations.layout !== 'object') {
                this.customizations.layout = {};
                for (const cat of this.categories) {
                    let ids = cat.items.map(i => i.id);
                    // Migrasi dari legacy orders jika ada
                    const legacyOrder = this.customizations.orders && this.customizations.orders[cat.key];
                    if (legacyOrder && Array.isArray(legacyOrder)) {
                        ids.sort((a, b) => {
                            const idxA = legacyOrder.indexOf(a);
                            const idxB = legacyOrder.indexOf(b);
                            if (idxA === -1 && idxB === -1) return 0;
                            if (idxA === -1) return 1;
                            if (idxB === -1) return -1;
                            return idxA - idxB;
                        });
                    }
                    this.customizations.layout[cat.key] = ids;
                }
            }

            // Bersihkan item yang tidak lagi tersedia & kumpulkan ID yang sudah terpasang
            for (const cat of this.categories) {
                if (!Array.isArray(this.customizations.layout[cat.key])) {
                    this.customizations.layout[cat.key] = [];
                }
                this.customizations.layout[cat.key] = this.customizations.layout[cat.key].filter(id => {
                    if (!allMap[id] || assigned.has(id)) return false;
                    assigned.add(id);
                    return true;
                });
            }

            // Tambahkan item baru yang belum terdaftar ke kategori default-nya
            for (const cat of this.categories) {
                for (const item of cat.items) {
                    if (!assigned.has(item.id)) {
                        this.customizations.layout[cat.key].push(item.id);
                        assigned.add(item.id);
                    }
                }
            }
        },

        loadCustomizations() {
            try {
                const raw = localStorage.getItem(this.storageKey);
                if (raw) {
                    this.customizations = JSON.parse(raw) || {};
                }
            } catch (e) {
                this.customizations = {};
            }
        },

        saveCustomizations() {
            try {
                localStorage.setItem(this.storageKey, JSON.stringify(this.customizations));
            } catch (e) {}
        },

        resetCustomizations() {
            if (confirm('Kembalikan semua susunan urutan, judul grup, nama, dan warna menu ke default bawaan?')) {
                localStorage.removeItem(this.storageKey);
                this.customizations = {};
                this.ensureLayout();
                this.isEditMode = false;
            }
        },

        getCategoryItems(catKey) {
            const allMap = this.getAllItemsMap();
            const ids = (this.customizations.layout && this.customizations.layout[catKey]) || [];
            return ids.map(id => allMap[id]).filter(Boolean);
        },

        getCategoryTitle(cat) {
            if (this.customizations.categories && this.customizations.categories[cat.key] && this.customizations.categories[cat.key].title) {
                return this.customizations.categories[cat.key].title;
            }
            return cat.title;
        },

        getCategorySubtitle(cat) {
            if (this.customizations.categories && this.customizations.categories[cat.key] && typeof this.customizations.categories[cat.key].subtitle === 'string') {
                return this.customizations.categories[cat.key].subtitle;
            }
            return cat.subtitle;
        },

        hasCustomCategoryColor(cat) {
            return !!(this.customizations.categories && this.customizations.categories[cat.key] && this.customizations.categories[cat.key].color);
        },

        getCategoryIndicatorStyle(cat) {
            const colorName = this.customizations.categories && this.customizations.categories[cat.key] && this.customizations.categories[cat.key].color;
            if (!colorName) return '';
            const p = this.getPaletteObj(colorName);
            return `background: ${p.previewHex};`;
        },

        openCategoryEditModal(cat) {
            this.editingCategory = cat;
            const saved = (this.customizations.categories && this.customizations.categories[cat.key]) || {};
            this.categoryForm = {
                key: cat.key,
                title: saved.title || cat.title,
                subtitle: typeof saved.subtitle === 'string' ? saved.subtitle : cat.subtitle,
                color: saved.color || 'teal'
            };
        },

        saveCategoryEdit() {
            if (!this.editingCategory) return;
            const key = this.editingCategory.key;
            if (!this.customizations.categories) {
                this.customizations.categories = {};
            }
            this.customizations.categories[key] = {
                title: (this.categoryForm.title || '').trim() || this.editingCategory.title,
                subtitle: (this.categoryForm.subtitle || '').trim(),
                color: this.categoryForm.color || ''
            };
            this.saveCustomizations();
            this.editingCategory = null;
        },

        handleCardPointerDown(e, catKey, item, cardEl) {
            if (!this.isEditMode || !cardEl) return;
            if (e.button && e.button !== 0) return;

            const startX = e.clientX;
            const startY = e.clientY;
            this.pendingDrag = { catKey, item, cardEl, startX, startY };

            clearTimeout(this.holdTimer);
            this.holdTimer = setTimeout(() => {
                if (this.pendingDrag && this.pendingDrag.item.id === item.id) {
                    this.beginItemDrag({ clientX: this.pendingDrag.startX, clientY: this.pendingDrag.startY }, catKey, item, cardEl);
                    this.pendingDrag = null;
                }
            }, 170);
        },

        beginItemDrag(e, catKey, item, cardEl) {
            if (!this.isEditMode || !cardEl || this.isDropping) return;
            clearTimeout(this.holdTimer);
            this.pendingDrag = null;

            const rect = cardEl.getBoundingClientRect();
            const offsetX = Math.min(Math.max(e.clientX - rect.left, 20), rect.width - 20);
            const offsetY = Math.min(Math.max(e.clientY - rect.top, 20), rect.height - 20);

            this.lastPointer = { x: e.clientX, y: e.clientY };
            this.dragPos = {
                x: e.clientX - offsetX,
                y: e.clientY - offsetY,
                width: rect.width,
                offsetX,
                offsetY
            };
            this.draggingItem = { id: item.id, catKey, item };
            this.justDragged = true;
            this.lastSwapAt = 0;

            if (navigator.vibrate) {
                try { navigator.vibrate(14); } catch (_) {}
            }

            this.startDragRafLoop();
        },

        startDragRafLoop() {
            if (this.dragRafId) cancelAnimationFrame(this.dragRafId);
            const tick = () => {
                if (!this.draggingItem || this.isDropping) {
                    this.dragRafId = null;
                    return;
                }

                // Update posisi ghost card langsung via GPU transform (60fps mulus tanpa re-render Alpine)
                const gx = this.lastPointer.x - this.dragPos.offsetX;
                const gy = this.lastPointer.y - this.dragPos.offsetY;
                const ghostEl = this.$refs.dragGhost;
                if (ghostEl) {
                    ghostEl.style.transform = `translate3d(${gx}px, ${gy}px, 0) scale(1.04) rotate(-0.8deg)`;
                }

                // Smooth auto-scroll kontinu saat jari dekat tepi atas/bawah area scroll
                const scrollEl = this.$refs.sheetScrollArea;
                if (scrollEl) {
                    const sr = scrollEl.getBoundingClientRect();
                    const edgeZone = 72;
                    let scrolled = false;
                    if (this.lastPointer.y < sr.top + edgeZone) {
                        const ratio = Math.min(1, (sr.top + edgeZone - this.lastPointer.y) / edgeZone);
                        scrollEl.scrollTop -= Math.max(3, Math.round(ratio * 14));
                        scrolled = true;
                    } else if (this.lastPointer.y > sr.bottom - edgeZone) {
                        const ratio = Math.min(1, (this.lastPointer.y - (sr.bottom - edgeZone)) / edgeZone);
                        scrollEl.scrollTop += Math.max(3, Math.round(ratio * 14));
                        scrolled = true;
                    }
                    if (scrolled) {
                        this.detectDragTarget(this.lastPointer.x, this.lastPointer.y);
                    }
                }

                this.dragRafId = requestAnimationFrame(tick);
            };
            this.dragRafId = requestAnimationFrame(tick);
        },

        onItemPointerMove(e) {
            // Jika sedang menunggu long-press pada kartu, cek apakah user menggeser jari
            if (this.pendingDrag && !this.draggingItem) {
                const dx = Math.abs(e.clientX - this.pendingDrag.startX);
                const dy = Math.abs(e.clientY - this.pendingDrag.startY);
                if (dx > 10 && dx > dy) {
                    const p = this.pendingDrag;
                    this.beginItemDrag(e, p.catKey, p.item, p.cardEl);
                } else if (dy > 12) {
                    clearTimeout(this.holdTimer);
                    this.pendingDrag = null;
                }
                return;
            }

            if (!this.draggingItem || this.isDropping) return;

            this.lastPointer.x = e.clientX;
            this.lastPointer.y = e.clientY;

            this.detectDragTarget(e.clientX, e.clientY);
        },

        detectDragTarget(clientX, clientY) {
            if (!this.draggingItem || this.isDropping) return;

            // Cooldown tipis (130ms) agar kartu tidak jitter / bolak-balik saat bergeser
            const now = performance.now();
            if (now - this.lastSwapAt < 130) return;

            const el = document.elementFromPoint(clientX, clientY);
            if (!el) return;

            const targetCard = el.closest('[data-menu-item-id]');
            if (targetCard) {
                const targetId = targetCard.getAttribute('data-menu-item-id');
                const targetCat = targetCard.getAttribute('data-menu-cat');
                if (targetCat && targetId && targetId !== this.draggingItem.id) {
                    this.lastSwapAt = now;
                    this.moveItemAcrossCategories(this.draggingItem.catKey, targetCat, this.draggingItem.id, targetId);
                }
                return;
            }

            const dropCatEl = el.closest('[data-drop-cat]');
            if (dropCatEl) {
                const targetCat = dropCatEl.getAttribute('data-drop-cat');
                if (targetCat && targetCat !== this.draggingItem.catKey) {
                    this.lastSwapAt = now;
                    this.moveItemAcrossCategories(this.draggingItem.catKey, targetCat, this.draggingItem.id, null);
                }
            }
        },

        captureCardPositions() {
            const positions = {};
            const root = this.$refs.sheetScrollArea;
            if (!root) return positions;
            root.querySelectorAll('[data-menu-item-id]').forEach(el => {
                const id = el.getAttribute('data-menu-item-id');
                if (id) {
                    const r = el.getBoundingClientRect();
                    positions[id] = { left: r.left, top: r.top };
                }
            });
            return positions;
        },

        playFlipAnimation(oldPositions, excludeId) {
            this.$nextTick(() => {
                const root = this.$refs.sheetScrollArea;
                if (!root) return;
                const cardsToAnimate = [];
                root.querySelectorAll('[data-menu-item-id]').forEach(el => {
                    const id = el.getAttribute('data-menu-item-id');
                    if (!id || id === excludeId || !oldPositions[id]) return;
                    const newRect = el.getBoundingClientRect();
                    const dx = oldPositions[id].left - newRect.left;
                    const dy = oldPositions[id].top - newRect.top;
                    if (Math.abs(dx) > 0.5 || Math.abs(dy) > 0.5) {
                        el.style.transition = 'none';
                        el.style.transform = `translate3d(${dx}px, ${dy}px, 0)`;
                        cardsToAnimate.push(el);
                    }
                });

                if (cardsToAnimate.length === 0) return;

                // Force reflow sekali untuk semua kartu
                void root.offsetHeight;

                requestAnimationFrame(() => {
                    cardsToAnimate.forEach(el => {
                        el.style.transition = 'transform 240ms cubic-bezier(0.22, 1, 0.36, 1)';
                        el.style.transform = 'translate3d(0, 0, 0)';
                        setTimeout(() => {
                            if (el.style) {
                                el.style.transition = '';
                                el.style.transform = '';
                            }
                        }, 260);
                    });
                });
            });
        },

        moveItemAcrossCategories(fromCat, toCat, draggedId, targetId) {
            this.ensureLayout();
            const sourceList = [...(this.customizations.layout[fromCat] || [])];
            const fromIdx = sourceList.indexOf(draggedId);
            if (fromIdx === -1) return;

            // Rekam posisi awal semua kartu untuk animasi FLIP yang mulus
            const oldPositions = this.captureCardPositions();

            if (fromCat === toCat) {
                const toIdx = sourceList.indexOf(targetId);
                if (toIdx === -1 || fromIdx === toIdx) return;
                sourceList.splice(fromIdx, 1);
                sourceList.splice(toIdx, 0, draggedId);
                this.customizations.layout[fromCat] = sourceList;
            } else {
                sourceList.splice(fromIdx, 1);
                const targetList = [...(this.customizations.layout[toCat] || [])];
                if (targetId) {
                    const toIdx = targetList.indexOf(targetId);
                    if (toIdx !== -1) {
                        targetList.splice(toIdx, 0, draggedId);
                    } else {
                        targetList.push(draggedId);
                    }
                } else {
                    targetList.push(draggedId);
                }
                this.customizations.layout[fromCat] = sourceList;
                this.customizations.layout[toCat] = targetList;
                this.draggingItem.catKey = toCat;
            }

            this.saveCustomizations();
            this.playFlipAnimation(oldPositions, draggedId);

            if (navigator.vibrate) {
                try { navigator.vibrate(8); } catch (_) {}
            }
        },

        endItemDrag() {
            clearTimeout(this.holdTimer);
            this.pendingDrag = null;
            if (!this.draggingItem || this.isDropping) return;

            if (this.dragRafId) {
                cancelAnimationFrame(this.dragRafId);
                this.dragRafId = null;
            }

            // Animasi snap-drop mulus menuju slot tujuan
            const root = this.$refs.sheetScrollArea;
            const ghostEl = this.$refs.dragGhost;
            const placeholderEl = root ? root.querySelector(`[data-menu-item-id="${this.draggingItem.id}"]`) : null;

            if (ghostEl && placeholderEl) {
                this.isDropping = true;
                const pr = placeholderEl.getBoundingClientRect();
                ghostEl.style.transition = 'transform 190ms cubic-bezier(0.22, 1, 0.36, 1), box-shadow 190ms ease, opacity 190ms ease';
                ghostEl.style.transform = `translate3d(${pr.left}px, ${pr.top}px, 0) scale(1) rotate(0deg)`;
                ghostEl.style.boxShadow = '0 4px 12px rgba(15, 23, 42, 0.08)';
                setTimeout(() => {
                    this.draggingItem = null;
                    this.isDropping = false;
                    setTimeout(() => { this.justDragged = false; }, 100);
                }, 195);
            } else {
                this.draggingItem = null;
                setTimeout(() => { this.justDragged = false; }, 100);
            }
        },

        openEditModal(item) {
            this.editingItem = item;
            this.editForm = {
                id: item.id,
                title: this.getItemTitle(item),
                subtitle: this.getItemSubtitle(item),
                color: this.getItemColor(item)
            };
        },

        saveItemEdit() {
            if (!this.editingItem) return;
            const id = this.editingItem.id;
            if (!this.customizations.items) {
                this.customizations.items = {};
            }
            this.customizations.items[id] = {
                title: this.editForm.title,
                subtitle: this.editForm.subtitle,
                color: this.editForm.color
            };
            this.saveCustomizations();
            this.editingItem = null;
        },

        getItemTitle(item) {
            if (this.customizations.items && this.customizations.items[item.id] && this.customizations.items[item.id].title) {
                return this.customizations.items[item.id].title;
            }
            return item.defaultTitle;
        },

        getItemSubtitle(item) {
            if (this.customizations.items && this.customizations.items[item.id] && this.customizations.items[item.id].subtitle) {
                return this.customizations.items[item.id].subtitle;
            }
            return item.defaultSubtitle;
        },

        getItemColor(item) {
            if (this.customizations.items && this.customizations.items[item.id] && this.customizations.items[item.id].color) {
                return this.customizations.items[item.id].color;
            }
            return item.defaultColor || 'teal';
        },

        getPaletteObj(colorName) {
            return this.colorPalette.find(c => c.name === colorName) || this.colorPalette[0];
        },

        getPaletteHex(colorName) {
            return this.getPaletteObj(colorName).previewHex;
        },

        hasCustomColor(item) {
            return !!(this.customizations.items && this.customizations.items[item.id] && this.customizations.items[item.id].color);
        },

        getCardStyle(item) {
            const p = this.getPaletteObj(this.getItemColor(item));
            if (this.draggingItem && this.draggingItem.id === item.id) {
                return 'border: 2px dashed #0d9488; background: #f0fdfa; opacity: 0.35;';
            }
            if (this.isEditMode) {
                return 'border: 1px solid #99f6e4; background: #ffffff; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);';
            }
            if (this.hasCustomColor(item)) {
                return `border: 1px solid ${p.border}; background: linear-gradient(135deg, #ffffff 60%, ${p.bg} 100%);`;
            }
            return 'border: 1px solid rgba(226, 232, 240, 0.9); background: #ffffff;';
        },

        getIconBoxStyle(item) {
            const p = this.getPaletteObj(this.getItemColor(item));
            return `background: ${p.bg}; color: ${p.previewHex}; border: 1px solid ${p.border};`;
        },

        isItemMatch(item) {
            if (!this.sheetSearch) return true;
            const q = this.sheetSearch.toLowerCase().trim();
            const t = this.getItemTitle(item).toLowerCase();
            const sub = this.getItemSubtitle(item).toLowerCase();
            const kw = (item.keywords || '').toLowerCase();
            return t.includes(q) || sub.includes(q) || kw.includes(q);
        },

        isCategoryMatch(cat) {
            if (!this.sheetSearch) return true;
            return this.getCategoryItems(cat.key).some(item => item.visible && this.isItemMatch(item));
        },

        startDrag(e) {
            if (this.isEditMode) return;
            this.isDragging = true;
            this.dragStartY = e.touches ? e.touches[0].clientY : e.clientY;
            this.sheetTranslateY = 0;
        },
        onDrag(e) {
            if (!this.isDragging || this.isEditMode) return;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            const deltaY = clientY - this.dragStartY;
            if (deltaY > 0) {
                this.sheetTranslateY = deltaY;
            } else {
                this.sheetTranslateY = deltaY * 0.15;
            }
        },
        endDrag() {
            if (!this.isDragging || this.isEditMode) return;
            const threshold = 55;
            if (this.sheetTranslateY > threshold) {
                this.isDragging = false;
                this.sheetTranslateY = 0;
                this.bottomSheetOpen = false;
            } else {
                this.isDragging = false;
                this.sheetTranslateY = 0;
            }
        }
    };
}
</script>
