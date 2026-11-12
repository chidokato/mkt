@extends('layout')

@section('title', 'Đăng bài hàng loạt & Hẹn giờ Pages - Meta Ads Hub')

@section('header_actions')
    <a href="{{ route('pages.index') }}" 
       class="inline-flex items-center px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl shadow-sm transition whitespace-nowrap">
        <i class="fa-solid fa-arrow-left text-xs mr-1.5"></i> Danh sách Pages
    </a>

    <button type="button"
            onclick="document.getElementById('modalPublishToken').classList.remove('hidden')"
            class="inline-flex items-center px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm transition whitespace-nowrap">
        <i class="fa-solid fa-key text-xs mr-1.5"></i> Nhập Token / Hướng dẫn quyền
    </button>

    <a href="{{ route('auth.facebook', ['mode' => 'publish']) }}" 
       class="inline-flex items-center px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-sm transition whitespace-nowrap">
        <i class="fa-brands fa-facebook text-xs mr-1.5"></i> Cấp quyền đăng bài (pages_manage_posts)
    </a>
@endsection

@section('content')
<div class="space-y-6">

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl p-4 text-xs space-y-1">
            <div class="font-bold flex items-center gap-1.5">
                <i class="fa-solid fa-circle-exclamation text-rose-600"></i> Vui lòng kiểm tra lại dữ liệu nhập:
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Pages sẵn sàng</span>
                <i class="fa-solid fa-flag text-sky-500"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900">{{ number_format($postStats['publishable_pages']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Có Page Access Token</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Pages Hot</span>
                <i class="fa-solid fa-fire text-orange-500"></i>
            </div>
            <div class="text-2xl font-bold text-orange-600">{{ number_format($postStats['hot_pages']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Ưu tiên lên đầu</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Đã đăng</span>
                <i class="fa-solid fa-circle-check text-emerald-500"></i>
            </div>
            <div class="text-2xl font-bold text-emerald-600">{{ number_format($postStats['published']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Đăng thành công</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Đang hẹn giờ</span>
                <i class="fa-solid fa-clock text-sky-500"></i>
            </div>
            <div class="text-2xl font-bold text-sky-600">{{ number_format($postStats['scheduled']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Chờ Meta tự đăng</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Thất bại</span>
                <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
            </div>
            <div class="text-2xl font-bold text-rose-600">{{ number_format($postStats['failed']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Cần kiểm tra lại</div>
        </div>
    </div>

    <!-- Main Bulk Composer Form -->
    <form action="{{ route('pages.publish.store') }}" method="POST" enctype="multipart/form-data" id="bulkPublishForm" onsubmit="handlePublishSubmit()">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- LEFT: Select Target Pages -->
            <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col overflow-hidden">
                <div class="p-4 border-b border-slate-200 bg-slate-50/70 space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-slate-900 flex items-center">
                            <i class="fa-solid fa-list-check text-sky-600 mr-2"></i> Chọn Fanpage đăng bài
                        </h2>
                        <span id="selectedPagesBadge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-100 text-sky-800 border border-sky-200">
                            Đã chọn: 0
                        </span>
                    </div>

                    <!-- Search & Quick Filter -->
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                            <input type="text"
                                   id="pagePickerSearch"
                                   oninput="filterPagePicker()"
                                   placeholder="Tìm tên Page hoặc Page ID..."
                                   class="w-full pl-8 pr-3 py-1.5 border border-slate-200 rounded-xl text-xs bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                        </div>
                        <select id="pagePickerFilter"
                                onchange="filterPagePicker()"
                                class="py-1.5 px-2.5 border border-slate-200 rounded-xl text-xs bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20">
                            <option value="all">Tất cả</option>
                            <option value="hot">Chỉ Pages Hot</option>
                            <option value="personal">Trang cá nhân</option>
                            <option value="owned">BM Sở hữu</option>
                        </select>
                    </div>

                    <!-- Quick Action Buttons -->
                    <div class="flex flex-wrap items-center gap-1.5">
                        <button type="button" onclick="selectVisiblePages(true)"
                                class="px-2.5 py-1 bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 rounded-lg text-[11px] font-semibold transition">
                            <i class="fa-solid fa-check-double mr-1"></i> Chọn đang hiện
                        </button>
                        <button type="button" onclick="selectHotPages()"
                                class="px-2.5 py-1 bg-orange-50 hover:bg-orange-100 text-orange-700 border border-orange-200 rounded-lg text-[11px] font-semibold transition">
                            <i class="fa-solid fa-fire mr-1"></i> Chọn Pages Hot
                        </button>
                        <button type="button" onclick="selectVisiblePages(false)"
                                class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-[11px] font-semibold transition">
                            Bỏ chọn tất cả
                        </button>
                    </div>
                </div>

                <!-- Scrollable Pages List -->
                <div class="divide-y divide-slate-100 overflow-y-auto max-h-[540px] flex-1" id="pagePickerList">
                    @forelse($pages as $p)
                        @php
                            $canPublish = !empty($p->access_token);
                            $oldSelected = is_array(old('page_ids')) && in_array((string) $p->page_id, old('page_ids'));
                        @endphp
                        <label class="page-picker-item flex items-center justify-between gap-3 px-4 py-2.5 transition {{ $canPublish ? 'hover:bg-slate-50 cursor-pointer' : 'opacity-55 bg-slate-50/70 cursor-not-allowed' }} {{ $p->is_hot ? 'bg-orange-50/30' : '' }}"
                               data-name="{{ mb_strtolower($p->name) }}"
                               data-id="{{ $p->page_id }}"
                               data-hot="{{ $p->is_hot ? '1' : '0' }}"
                               data-source="{{ $p->source_type }}">
                            <div class="flex items-center space-x-3 min-w-0">
                                <input type="checkbox"
                                       name="page_ids[]"
                                       value="{{ $p->page_id }}"
                                       {{ $canPublish ? '' : 'disabled' }}
                                       {{ $oldSelected ? 'checked' : '' }}
                                       onchange="updateSelectedCount()"
                                       class="page-picker-checkbox w-4 h-4 text-sky-600 border-slate-300 rounded focus:ring-sky-500 flex-shrink-0">
                                <img src="{{ $p->display_avatar }}"
                                     alt="{{ $p->name }}"
                                     class="w-8 h-8 rounded-lg object-cover border border-slate-200 flex-shrink-0"
                                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($p->name) }}&background=0284c7&color=ffffff&size=80'">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-bold text-slate-800 truncate" title="{{ $p->name }}">
                                            {{ $p->name }}
                                        </span>
                                        @if($p->is_hot)
                                            <i class="fa-solid fa-fire text-[11px] text-orange-500 flex-shrink-0" title="Page Hot"></i>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 text-[10px] text-slate-500 mt-0.5">
                                        <span class="font-mono">{{ $p->page_id }}</span>
                                        <span>&bull;</span>
                                        <span>{{ $p->source_label }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-shrink-0 text-right">
                                @if(!$canPublish)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-200 text-slate-600" title="Page này chưa có Page Access Token">
                                        Thiếu Token
                                    </span>
                                @elseif($p->last_post_at)
                                    <span class="text-[10px] text-slate-400" title="Lần đăng gần nhất">
                                        {{ $p->last_post_at->copy()->timezone('Asia/Ho_Chi_Minh')->format('d/m') }}
                                    </span>
                                @endif
                            </div>
                        </label>
                    @empty
                        <div class="p-10 text-center text-slate-500 text-xs">
                            Chưa có Facebook Page nào. Hãy đồng bộ Pages trước khi đăng bài.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- RIGHT: Composer & Scheduling Controls -->
            <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                <div class="p-4 border-b border-slate-200 bg-slate-50/70">
                    <h2 class="text-sm font-bold text-slate-900 flex items-center">
                        <i class="fa-solid fa-pen-nib text-rose-500 mr-2"></i> Soạn nội dung & Lịch đăng bài
                    </h2>
                </div>

                <div class="p-5 space-y-5 flex-1">
                    <!-- Post Message -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase">Nội dung bài viết (Caption)</label>
                            <span id="messageCharCount" class="text-[11px] font-mono text-slate-400">0 ký tự</span>
                        </div>
                        <textarea name="message"
                                  id="postMessageInput"
                                  rows="5"
                                  oninput="updateCharCount()"
                                  placeholder="Nhập nội dung bài viết sẽ đăng đồng loạt lên các Fanpage đã chọn..."
                                  class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs sm:text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 leading-relaxed">{{ old('message') }}</textarea>
                    </div>

                    <!-- Attachment Mode Tabs -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Đính kèm nội dung</label>
                        @php
                            $oldAttachment = old('attachment_type', 'none');
                        @endphp
                        <div class="grid grid-cols-3 gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="attachment_type" value="none" class="peer sr-only" {{ $oldAttachment === 'none' ? 'checked' : '' }} onchange="toggleAttachmentSection('none')">
                                <div class="p-2.5 rounded-xl border border-slate-200 peer-checked:border-sky-500 peer-checked:bg-sky-50 peer-checked:text-sky-700 text-center text-xs font-semibold text-slate-600 transition">
                                    <i class="fa-solid fa-align-left mr-1.5"></i> Chỉ văn bản
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="attachment_type" value="link" class="peer sr-only" {{ $oldAttachment === 'link' ? 'checked' : '' }} onchange="toggleAttachmentSection('link')">
                                <div class="p-2.5 rounded-xl border border-slate-200 peer-checked:border-sky-500 peer-checked:bg-sky-50 peer-checked:text-sky-700 text-center text-xs font-semibold text-slate-600 transition">
                                    <i class="fa-solid fa-link mr-1.5"></i> Kèm Link
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="attachment_type" value="photo" class="peer sr-only" {{ $oldAttachment === 'photo' ? 'checked' : '' }} onchange="toggleAttachmentSection('photo')">
                                <div class="p-2.5 rounded-xl border border-slate-200 peer-checked:border-sky-500 peer-checked:bg-sky-50 peer-checked:text-sky-700 text-center text-xs font-semibold text-slate-600 transition">
                                    <i class="fa-regular fa-images mr-1.5"></i> Kèm Hình ảnh
                                </div>
                            </label>
                        </div>

                        <!-- Link Input Box -->
                        <div id="attachmentLinkBox" class="mt-3 {{ $oldAttachment === 'link' ? '' : 'hidden' }}">
                            <input type="url"
                                   name="link_url"
                                   value="{{ old('link_url') }}"
                                   placeholder="https://example.com/du-an-bat-dong-san"
                                   class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs font-mono focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                            <p class="text-[11px] text-slate-400 mt-1">Facebook sẽ tự động tạo thẻ xem trước (preview) cho liên kết này.</p>
                        </div>

                        <!-- Photo Upload / URL Box -->
                        <div id="attachmentPhotoBox" class="mt-3 space-y-3 {{ $oldAttachment === 'photo' ? '' : 'hidden' }}">
                            <div class="border-2 border-dashed border-slate-300 rounded-xl p-4 bg-slate-50/70 hover:border-sky-400 transition">
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5 cursor-pointer">
                                    <i class="fa-solid fa-cloud-arrow-up text-sky-600 mr-1.5"></i> Tải ảnh từ máy tính (tối đa 10 ảnh, JPG/PNG/WebP)
                                </label>
                                <input type="file"
                                       name="images[]"
                                       id="photoFilesInput"
                                       accept="image/jpeg,image/png,image/webp,image/gif"
                                       multiple
                                       onchange="previewSelectedImages(this)"
                                       class="block w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-100 file:text-sky-700 hover:file:bg-sky-200 cursor-pointer">
                                <div id="photoPreviewGrid" class="grid grid-cols-5 gap-2 mt-3 hidden"></div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Hoặc dán link ảnh công khai (mỗi dòng 1 link URL):</label>
                                <textarea name="image_urls"
                                          rows="2"
                                          placeholder="https://example.com/anh-1.jpg&#10;https://example.com/anh-2.jpg"
                                          class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-mono focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">{{ old('image_urls') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Publish Mode & Scheduling -->
                    <div class="pt-4 border-t border-slate-100 space-y-3">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Chế độ đăng bài</label>
                        @php
                            $oldPublishMode = old('publish_mode', 'now');
                        @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="publish_mode" value="now" class="peer sr-only" {{ $oldPublishMode === 'now' ? 'checked' : '' }} onchange="toggleScheduleBox('now')">
                                <div class="p-3 rounded-xl border border-slate-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50/70 transition flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-bolt"></i>
                                    </span>
                                    <div>
                                        <div class="text-xs font-bold text-slate-800">Đăng ngay lập tức</div>
                                        <div class="text-[11px] text-slate-500">Xuất bản ngay lên các Page đã chọn</div>
                                    </div>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="publish_mode" value="scheduled" class="peer sr-only" {{ $oldPublishMode === 'scheduled' ? 'checked' : '' }} onchange="toggleScheduleBox('scheduled')">
                                <div class="p-3 rounded-xl border border-slate-200 peer-checked:border-sky-500 peer-checked:bg-sky-50/70 transition flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-calendar-check"></i>
                                    </span>
                                    <div>
                                        <div class="text-xs font-bold text-slate-800">Hẹn giờ đăng (Meta Schedule)</div>
                                        <div class="text-[11px] text-slate-500">Từ 10 phút đến 30 ngày tới</div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Schedule Options Box -->
                        <div id="scheduleOptionsBox" class="p-4 bg-sky-50/60 border border-sky-200 rounded-xl space-y-3 {{ $oldPublishMode === 'scheduled' ? '' : 'hidden' }}">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-sky-900 mb-1">
                                        Thời gian bắt đầu đăng (Giờ VN) <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="datetime-local"
                                           name="scheduled_at"
                                           id="scheduledAtInput"
                                           value="{{ old('scheduled_at', $defaultScheduleLocal) }}"
                                           min="{{ $minScheduleLocal }}"
                                           max="{{ $maxScheduleLocal }}"
                                           onchange="updateScheduleSummary()"
                                           class="w-full px-3 py-2 bg-white border border-sky-300 rounded-xl text-xs font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-sky-900 mb-1">
                                        Giãn cách giữa từng Page
                                    </label>
                                    @php $oldStagger = (int) old('stagger_minutes', 0); @endphp
                                    <select name="stagger_minutes"
                                            id="staggerMinutesSelect"
                                            onchange="updateScheduleSummary()"
                                            class="w-full px-3 py-2 bg-white border border-sky-300 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500">
                                        <option value="0" {{ $oldStagger === 0 ? 'selected' : '' }}>Đăng cùng 1 thời điểm (0 phút)</option>
                                        <option value="2" {{ $oldStagger === 2 ? 'selected' : '' }}>Cách nhau 2 phút / Page</option>
                                        <option value="5" {{ $oldStagger === 5 ? 'selected' : '' }}>Cách nhau 5 phút / Page</option>
                                        <option value="10" {{ $oldStagger === 10 ? 'selected' : '' }}>Cách nhau 10 phút / Page</option>
                                        <option value="15" {{ $oldStagger === 15 ? 'selected' : '' }}>Cách nhau 15 phút / Page</option>
                                        <option value="30" {{ $oldStagger === 30 ? 'selected' : '' }}>Cách nhau 30 phút / Page</option>
                                        <option value="60" {{ $oldStagger === 60 ? 'selected' : '' }}>Cách nhau 60 phút / Page</option>
                                    </select>
                                </div>
                            </div>
                            <div id="scheduleSummaryText" class="text-[11px] text-sky-800 flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-info text-sky-600"></i>
                                <span>Facebook yêu cầu lịch hẹn tối thiểu sau hiện tại 10 phút và tối đa 30 ngày.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Bar -->
                <div class="px-5 py-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="text-xs text-slate-500">
                        Bài viết sẽ được gửi qua Page Access Token của từng Fanpage.
                    </div>
                    <button type="submit"
                            id="btnSubmitBulkPublish"
                            class="inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-orange-500 to-rose-500 hover:from-orange-600 hover:to-rose-600 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm transition cursor-pointer">
                        <i class="fa-solid fa-paper-plane mr-2" id="submitPublishIcon"></i>
                        <span id="submitPublishText">Thực hiện đăng bài</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Post History & Scheduled Posts Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 sm:px-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-slate-50/50">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center">
                    <i class="fa-solid fa-clock-rotate-left text-indigo-600 mr-2"></i> Lịch sử đăng bài & Bài đang hẹn giờ
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Theo dõi kết quả từng Page, mở xem bài đã đăng hoặc hủy bài đang lên lịch.</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <form action="{{ route('pages.posts.syncStatus') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center px-3 py-1.5 bg-white hover:bg-sky-50 text-sky-700 border border-sky-200 rounded-xl text-xs font-semibold shadow-2xs transition cursor-pointer"
                            title="Kiểm tra trạng thái thực tế của các bài hẹn giờ trên Facebook">
                        <i class="fa-solid fa-arrows-rotate text-[11px] mr-1.5"></i> Cập nhật trạng thái
                    </button>
                </form>
                <div class="text-xs text-slate-500 font-medium">
                    Tổng <span class="font-bold text-slate-800">{{ $posts->total() }}</span> lượt gửi
                </div>
            </div>
        </div>

        @if($posts->isEmpty())
            <div class="p-10 text-center text-slate-500 text-xs">
                Chưa có lượt đăng bài hàng loạt nào. Hãy chọn các Fanpage ở phía trên và bắt đầu đăng bài đầu tiên!
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50 text-slate-600 text-[11px] uppercase font-semibold">
                        <tr>
                            <th class="px-4 py-2.5 text-left whitespace-nowrap">Fanpage</th>
                            <th class="px-4 py-2.5 text-left whitespace-nowrap">Nội dung & Đính kèm</th>
                            <th class="px-4 py-2.5 text-left whitespace-nowrap">Thời gian</th>
                            <th class="px-4 py-2.5 text-center whitespace-nowrap">Trạng thái</th>
                            <th class="px-4 py-2.5 text-center whitespace-nowrap">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($posts as $post)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="font-bold text-slate-800">{{ $post->page_name }}</div>
                                    <div class="text-[11px] font-mono text-slate-400">{{ $post->page_id }}</div>
                                </td>

                                <td class="px-4 py-2.5 max-w-lg">
                                    @if(!empty($post->message))
                                        <div class="text-slate-700 line-clamp-2">{{ \Illuminate\Support\Str::limit($post->message, 120) }}</div>
                                    @else
                                        <div class="text-slate-400 italic">(Không có nội dung văn bản)</div>
                                    @endif

                                    <div class="flex flex-wrap items-center gap-2 mt-1">
                                        @if($post->media_count > 0)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[10px] font-semibold border border-indigo-200">
                                                <i class="fa-regular fa-images mr-1"></i> {{ $post->media_count }} ảnh
                                            </span>
                                        @endif
                                        @if(!empty($post->link_url))
                                            <a href="{{ $post->link_url }}" target="_blank" rel="noopener noreferrer"
                                               class="inline-flex items-center px-1.5 py-0.5 rounded bg-sky-50 text-sky-700 text-[10px] font-medium border border-sky-200 hover:underline max-w-[220px] truncate">
                                                <i class="fa-solid fa-link mr-1"></i> {{ $post->link_url }}
                                            </a>
                                        @endif
                                    </div>

                                    @if($post->status === 'failed' && !empty($post->localized_error))
                                        <div class="mt-1.5 p-2 rounded-lg bg-rose-50 border border-rose-200 text-[11px] text-rose-700 leading-relaxed whitespace-normal break-words">
                                            <i class="fa-solid fa-circle-exclamation mr-1 text-rose-600"></i>
                                            <span>{{ $post->localized_error }}</span>
                                        </div>
                                    @endif
                                </td>

                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    @if($post->status === 'published' && $post->publish_mode === 'scheduled')
                                        <div class="font-semibold text-emerald-700">
                                            <i class="fa-regular fa-calendar-check mr-1"></i>
                                            {{ ($post->published_at ?? $post->scheduled_at)?->copy()->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}
                                        </div>
                                        <div class="text-[10px] text-emerald-600">Đã đăng theo lịch</div>
                                    @elseif($post->publish_mode === 'scheduled' && $post->scheduled_at)
                                        <div class="font-semibold text-sky-700">
                                            <i class="fa-regular fa-calendar-check mr-1"></i>
                                            {{ $post->scheduled_at->copy()->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}
                                        </div>
                                        <div class="text-[10px] text-slate-400">Hẹn giờ đăng</div>
                                    @elseif($post->published_at)
                                        <div class="font-semibold text-slate-700">
                                            {{ $post->published_at->copy()->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}
                                        </div>
                                        <div class="text-[10px] text-slate-400">Đăng ngay</div>
                                    @else
                                        <div class="text-slate-500">
                                            {{ $post->created_at->copy()->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-4 py-2.5 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $post->status_badge_class }}">
                                        {{ $post->status_label }}
                                    </span>
                                </td>

                                <td class="px-4 py-2.5 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        @if(!empty($post->permalink_url))
                                            <a href="{{ $post->permalink_url }}" target="_blank" rel="noopener noreferrer"
                                               class="inline-flex items-center px-2.5 py-1 bg-sky-50 hover:bg-sky-100 text-sky-700 rounded-lg text-[11px] font-semibold transition">
                                                <i class="fa-brands fa-facebook mr-1 text-blue-600"></i> Xem bài
                                            </a>
                                        @elseif($post->status === 'scheduled')
                                            <a href="https://business.facebook.com/latest/posts/scheduled_posts?asset_id={{ $post->page_id }}" target="_blank" rel="noopener noreferrer"
                                               class="inline-flex items-center px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[11px] font-semibold transition"
                                               title="Xem trong Meta Business Suite">
                                                <i class="fa-solid fa-arrow-up-right-from-square mr-1 text-[10px]"></i> Meta Suite
                                            </a>
                                        @endif

                                        @if($post->status === 'failed')
                                            @php
                                                $needsScheduleAdjust = ($post->publish_mode === 'scheduled') && (!$post->scheduled_at || $post->scheduled_at->lt(now()->addMinutes(11)));
                                                $missingOriginalMedia = ($post->media_count > 0 && empty($post->media_paths));
                                                $canInstantRetry = !$needsScheduleAdjust && !$missingOriginalMedia;
                                            @endphp
                                            @if($canInstantRetry)
                                                <form action="{{ route('pages.posts.retry', $post->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                            class="inline-flex items-center px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 rounded-lg text-[11px] font-semibold transition cursor-pointer">
                                                        <i class="fa-solid fa-rotate-right mr-1"></i> Thử lại
                                                    </button>
                                                </form>
                                            @else
                                                <button type="button"
                                                        onclick="openRetryModal(this)"
                                                        data-action="{{ route('pages.posts.retry', $post->id) }}"
                                                        data-page-name="{{ $post->page_name }}"
                                                        data-publish-mode="{{ $post->publish_mode }}"
                                                        data-missing-media="{{ $missingOriginalMedia ? '1' : '0' }}"
                                                        class="inline-flex items-center px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 rounded-lg text-[11px] font-semibold transition cursor-pointer">
                                                    <i class="fa-solid fa-rotate-right mr-1"></i> Thử lại
                                                </button>
                                            @endif
                                        @endif

                                        @if($post->status === 'scheduled')
                                            <form action="{{ route('pages.posts.destroy', $post->id) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Bạn có chắc muốn hủy bài đang hẹn giờ trên Page [{{ addslashes($post->page_name) }}]?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-[11px] font-semibold transition">
                                                    <i class="fa-solid fa-ban mr-1"></i> Hủy lịch
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('pages.posts.destroy', $post->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition"
                                                        title="Xóa khỏi lịch sử">
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($posts->hasPages())
                <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                    {{ $posts->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

<!-- MODAL: THỬ LẠI BÀI ĐĂNG THẤT BẠI (KHI CẦN CHỈNH GIỜ HẸN MỚI HOẶC ĐÍNH KÈM LẠI ẢNH) -->
<div id="modalRetryPost" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200 animate-in fade-in duration-200">
        <div class="bg-amber-600 px-6 py-4 flex justify-between items-center text-white">
            <h3 class="font-bold text-base flex items-center">
                <i class="fa-solid fa-rotate-right mr-2"></i> Thử lại đăng bài lên Page
            </h3>
            <button type="button" onclick="document.getElementById('modalRetryPost').classList.add('hidden')" class="text-white/80 hover:text-white text-xl font-bold">&times;</button>
        </div>

        <form id="retryPostForm" action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf

            <div class="text-xs text-slate-600">
                Page mục tiêu: <strong id="retryModalPageName" class="text-slate-900"></strong>
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700 uppercase">Chế độ gửi lại</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="publish_mode" value="scheduled" id="retryModeScheduled" class="peer sr-only" onchange="toggleRetryScheduleBox('scheduled')">
                        <div class="p-2.5 rounded-xl border border-slate-200 peer-checked:border-sky-500 peer-checked:bg-sky-50 peer-checked:text-sky-700 text-center text-xs font-semibold text-slate-600 transition">
                            <i class="fa-regular fa-clock mr-1"></i> Hẹn giờ mới
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="publish_mode" value="now" id="retryModeNow" class="peer sr-only" onchange="toggleRetryScheduleBox('now')">
                        <div class="p-2.5 rounded-xl border border-slate-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 text-center text-xs font-semibold text-slate-600 transition">
                            <i class="fa-solid fa-bolt mr-1"></i> Đăng ngay
                        </div>
                    </label>
                </div>
            </div>

            <div id="retryScheduleBox">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    Thời gian hẹn mới (Giờ VN, cách hiện tại ít nhất 11 phút)
                </label>
                <input type="datetime-local"
                       name="scheduled_at"
                       id="retryScheduledAtInput"
                       value="{{ $defaultScheduleLocal }}"
                       min="{{ $minScheduleLocal }}"
                       max="{{ $maxScheduleLocal }}"
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-mono focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>

            <div id="retryMissingMediaBox" class="hidden p-3 bg-amber-50 border border-amber-200 rounded-xl space-y-2">
                <div class="text-[11px] text-amber-800">
                    <i class="fa-solid fa-triangle-exclamation mr-1 text-amber-600"></i>
                    Lượt đăng cũ chưa lưu sẵn file ảnh gốc. Bạn có thể chọn lại ảnh bên dưới (hoặc bỏ trống để đăng lại nội dung chữ):
                </div>
                <input type="file"
                       name="images[]"
                       accept="image/jpeg,image/png,image/webp,image/gif"
                       multiple
                       class="block w-full text-xs text-slate-600 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-200 cursor-pointer">
            </div>

            <div class="pt-3 flex justify-end space-x-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modalRetryPost').classList.add('hidden')"
                        class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-xl transition font-semibold">
                    Hủy
                </button>
                <button type="submit"
                        class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    <i class="fa-solid fa-rotate-right mr-1.5"></i> Gửi lại ngay
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: NHẬP TOKEN CÓ QUYỀN ĐĂNG BÀI & HƯỚNG DẪN BẬT QUYỀN TRÊN META -->
<div id="modalPublishToken" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200 animate-in fade-in duration-200">
        <div class="bg-indigo-900 px-6 py-4 flex justify-between items-center text-white">
            <h3 class="font-bold text-base flex items-center">
                <i class="fa-solid fa-key mr-2 text-indigo-300"></i> Cấp quyền đăng bài (pages_manage_posts)
            </h3>
            <button type="button" onclick="document.getElementById('modalPublishToken').classList.add('hidden')" class="text-white/80 hover:text-white text-xl font-bold">&times;</button>
        </div>

        <form action="{{ route('pages.token') }}" method="POST" class="p-6 space-y-4">
            @csrf

            <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 space-y-1.5">
                <div class="font-bold flex items-center gap-1.5">
                    <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                    <span>Nếu bấm "Cấp quyền đăng bài" bị lỗi Invalid Scopes: pages_manage_posts:</span>
                </div>
                <ol class="list-decimal list-inside text-[11px] text-amber-800 space-y-1">
                    <li>Mở <a href="https://developers.facebook.com/apps/{{ $metaAppId }}/use_cases/" target="_blank" rel="noopener noreferrer" class="text-blue-700 font-semibold underline">Meta App Dashboard ({{ $metaAppId }}) &gt; Use cases</a>.</li>
                    <li>Chọn <strong>Manage everything on your Page</strong> &gt; <strong>Customize</strong> (hoặc Add use case này nếu chưa có).</li>
                    <li>Tại <strong>Permissions and features</strong>, bấm <strong>Add</strong> cho <code>pages_manage_posts</code> (trạng thái chuyển sang <em>Ready for testing</em>), sau đó bấm lại nút <strong>Cấp quyền đăng bài</strong>.</li>
                </ol>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    Hoặc dán Facebook User Access Token có quyền đăng bài
                </label>
                <textarea name="access_token"
                          rows="3"
                          required
                          placeholder="Dán mã Access Token (tạo từ Graph API Explorer có tích pages_show_list, pages_read_engagement, pages_manage_posts)"
                          class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                <p class="text-[11px] text-slate-500 mt-1">
                    Hệ thống sẽ tự động cập nhật Page Access Token mới cho cả Trang cá nhân lẫn Trang thuộc BM.
                </p>
            </div>

            <div class="pt-3 flex justify-end space-x-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modalPublishToken').classList.add('hidden')"
                        class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-xl transition font-semibold">
                    Đóng
                </button>
                <button type="submit"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    <i class="fa-solid fa-arrows-rotate mr-1.5"></i> Lưu Token & Cập nhật Pages
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function updateCharCount() {
        const input = document.getElementById('postMessageInput');
        const label = document.getElementById('messageCharCount');
        if (input && label) {
            label.textContent = (input.value || '').length.toLocaleString('vi-VN') + ' ký tự';
        }
    }

    function updateSelectedCount() {
        const checked = document.querySelectorAll('.page-picker-checkbox:checked').length;
        const badge = document.getElementById('selectedPagesBadge');
        if (badge) {
            badge.textContent = 'Đã chọn: ' + checked;
        }
        updateSubmitLabel(checked);
        updateScheduleSummary();
    }

    function updateSubmitLabel(checkedCount) {
        const mode = document.querySelector('input[name="publish_mode"]:checked')?.value || 'now';
        const textEl = document.getElementById('submitPublishText');
        if (!textEl) return;

        if (mode === 'scheduled') {
            textEl.textContent = checkedCount > 0 ? `Hẹn giờ đăng lên ${checkedCount} Pages` : 'Hẹn giờ đăng bài';
        } else {
            textEl.textContent = checkedCount > 0 ? `Đăng ngay lên ${checkedCount} Pages` : 'Đăng bài ngay';
        }
    }

    function filterPagePicker() {
        const q = (document.getElementById('pagePickerSearch')?.value || '').toLowerCase().trim();
        const filter = document.getElementById('pagePickerFilter')?.value || 'all';
        const items = document.querySelectorAll('.page-picker-item');

        items.forEach(item => {
            const name = item.dataset.name || '';
            const id = item.dataset.id || '';
            const isHot = item.dataset.hot === '1';
            const source = item.dataset.source || '';

            const matchSearch = !q || name.includes(q) || id.includes(q);
            let matchFilter = true;
            if (filter === 'hot') matchFilter = isHot;
            else if (filter === 'personal') matchFilter = source === 'personal';
            else if (filter === 'owned') matchFilter = source === 'owned';

            item.classList.toggle('hidden', !(matchSearch && matchFilter));
        });
    }

    function selectVisiblePages(checked) {
        const items = document.querySelectorAll('.page-picker-item');
        items.forEach(item => {
            const cb = item.querySelector('.page-picker-checkbox');
            if (!cb || cb.disabled) return;
            if (!checked || !item.classList.contains('hidden')) {
                cb.checked = checked;
            }
        });
        updateSelectedCount();
    }

    function selectHotPages() {
        const items = document.querySelectorAll('.page-picker-item');
        items.forEach(item => {
            const cb = item.querySelector('.page-picker-checkbox');
            if (!cb || cb.disabled) return;
            cb.checked = item.dataset.hot === '1';
        });
        updateSelectedCount();
    }

    function toggleAttachmentSection(type) {
        document.getElementById('attachmentLinkBox')?.classList.toggle('hidden', type !== 'link');
        document.getElementById('attachmentPhotoBox')?.classList.toggle('hidden', type !== 'photo');
    }

    function toggleScheduleBox(mode) {
        document.getElementById('scheduleOptionsBox')?.classList.toggle('hidden', mode !== 'scheduled');
        updateSelectedCount();
    }

    function previewSelectedImages(input) {
        const grid = document.getElementById('photoPreviewGrid');
        if (!grid) return;
        grid.textContent = '';

        const files = Array.from(input.files || []).slice(0, 10);
        if (files.length === 0) {
            grid.classList.add('hidden');
            return;
        }

        grid.classList.remove('hidden');
        files.forEach(file => {
            const wrapper = document.createElement('div');
            wrapper.className = 'relative rounded-lg overflow-hidden border border-slate-200 bg-white aspect-square';
            const img = document.createElement('img');
            img.className = 'w-full h-full object-cover';
            img.alt = file.name;
            const reader = new FileReader();
            reader.onload = e => {
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
            wrapper.appendChild(img);
            grid.appendChild(wrapper);
        });
    }

    function updateScheduleSummary() {
        const mode = document.querySelector('input[name="publish_mode"]:checked')?.value || 'now';
        const summary = document.getElementById('scheduleSummaryText');
        if (!summary || mode !== 'scheduled') return;

        const checked = document.querySelectorAll('.page-picker-checkbox:checked').length;
        const startVal = document.getElementById('scheduledAtInput')?.value;
        const stagger = parseInt(document.getElementById('staggerMinutesSelect')?.value || '0', 10);

        if (!startVal || checked === 0) {
            summary.querySelector('span').textContent = 'Facebook yêu cầu lịch hẹn tối thiểu sau hiện tại 10 phút và tối đa 30 ngày.';
            return;
        }

        const startDate = new Date(startVal);
        if (isNaN(startDate.getTime())) return;

        const endDate = new Date(startDate.getTime() + Math.max(0, checked - 1) * stagger * 60000);
        const fmt = d => d.toLocaleString('vi-VN', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric' });

        if (stagger > 0 && checked > 1) {
            summary.querySelector('span').textContent = `Dự kiến ${checked} Pages: Page đầu lúc ${fmt(startDate)} → Page cuối lúc ${fmt(endDate)}.`;
        } else {
            summary.querySelector('span').textContent = `Tất cả ${checked} Pages sẽ được lên lịch đăng lúc ${fmt(startDate)}.`;
        }
    }

    function handlePublishSubmit() {
        const btn = document.getElementById('btnSubmitBulkPublish');
        const icon = document.getElementById('submitPublishIcon');
        const text = document.getElementById('submitPublishText');
        if (btn) btn.disabled = true;
        if (icon) icon.className = 'fa-solid fa-circle-notch fa-spin mr-2';
        if (text) text.textContent = 'Đang gửi lên Facebook...';
    }

    function openRetryModal(btn) {
        const modal = document.getElementById('modalRetryPost');
        const form = document.getElementById('retryPostForm');
        const pageNameEl = document.getElementById('retryModalPageName');
        const missingMediaBox = document.getElementById('retryMissingMediaBox');
        const modeScheduled = document.getElementById('retryModeScheduled');
        const modeNow = document.getElementById('retryModeNow');

        form.action = btn.dataset.action || '';
        pageNameEl.textContent = btn.dataset.pageName || '';

        const isScheduled = btn.dataset.publishMode === 'scheduled';
        if (isScheduled) {
            modeScheduled.checked = true;
            toggleRetryScheduleBox('scheduled');
        } else {
            modeNow.checked = true;
            toggleRetryScheduleBox('now');
        }

        if (missingMediaBox) {
            missingMediaBox.classList.toggle('hidden', btn.dataset.missingMedia !== '1');
        }

        modal.classList.remove('hidden');
    }

    function toggleRetryScheduleBox(mode) {
        document.getElementById('retryScheduleBox')?.classList.toggle('hidden', mode !== 'scheduled');
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateCharCount();
        updateSelectedCount();

        @if(!empty($nextScheduledTimestampMs))
        const targetMs = {{ (int) $nextScheduledTimestampMs }};
        const delayMs = Math.max(2000, targetMs - Date.now());
        if (delayMs <= 3600000) {
            setTimeout(async function () {
                try {
                    const res = await fetch('{{ route('pages.posts.syncStatus') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ only_due: true }),
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if ((data.published_count || 0) > 0) {
                            window.location.reload();
                        }
                    }
                } catch (e) {
                    // bỏ qua lỗi mạng tạm thời
                }
            }, delayMs);
        }
        @endif
    });
</script>
@endpush
@endsection
