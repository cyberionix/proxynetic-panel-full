@extends("admin.template")
@section("title", "Otomatik Yanıt Kuralları")
@section("css")
    <style>
        .tiptap-wrapper {
            border: 1px solid #e4e6ef;
            border-radius: 0.475rem;
            overflow: hidden;
        }
        .tiptap-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 2px;
            padding: 8px 10px;
            background: #f9f9f9;
            border-bottom: 1px solid #e4e6ef;
        }
        .tiptap-toolbar button {
            background: none;
            border: 1px solid transparent;
            border-radius: 4px;
            padding: 5px 8px;
            cursor: pointer;
            color: #5e6278;
            font-size: 13px;
            line-height: 1;
            transition: all 0.15s ease;
        }
        .tiptap-toolbar button:hover { background: #e4e6ef; }
        .tiptap-toolbar button.is-active { background: #009ef7; color: #fff; border-color: #009ef7; }
        .tiptap-toolbar .separator { width: 1px; background: #e4e6ef; margin: 0 4px; }
        .tiptap-editor { min-height: 200px; max-height: 350px; overflow-y: auto; }
        .tiptap-editor .ProseMirror {
            padding: 12px 15px;
            min-height: 200px;
            outline: none;
            font-size: 14px;
            color: #181c32;
            line-height: 1.7;
            cursor: text;
        }
        .tiptap-editor .ProseMirror:focus { outline: none; }
        .tiptap-editor .ProseMirror p.is-editor-empty:first-child::before {
            content: attr(data-placeholder);
            float: left;
            color: #b5b5c3;
            pointer-events: none;
            height: 0;
        }
        .tiptap-editor .ProseMirror img { max-width: 100%; height: auto; border-radius: 4px; }
        .tiptap-editor .ProseMirror blockquote { border-left: 3px solid #009ef7; padding-left: 12px; margin-left: 0; color: #5e6278; }
        .tiptap-editor .ProseMirror a { color: #009ef7; text-decoration: underline; }
        .trigger-badge { font-size: 12px; padding: 6px 12px; }
        .rule-card { transition: all 0.2s ease; border-left: 4px solid transparent; }
        .rule-card:hover { box-shadow: 0 0 15px rgba(0,0,0,0.08); }
        .rule-card.active { border-left-color: #50cd89; }
        .rule-card.inactive { border-left-color: #f1416c; opacity: 0.7; }
    </style>
@endsection
@section("breadcrumb")
    <x-admin.bread-crumb :data="[__('support_tickets') => route('admin.supports.index'), 'Hazır Mesajlar' => route('admin.supports.templates.index'), 'Otomatik Yanıt Kuralları']"/>
@endsection
@section("master")
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="fw-bold"><i class="fa fa-robot text-primary me-2"></i>Otomatik Yanıt Kuralları</h3>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('admin.supports.templates.index') }}" class="btn btn-light-primary btn-sm me-2">
                    <i class="fa fa-envelope-open-text me-1"></i>Hazır Mesajlar
                </a>
                <button type="button" class="btn btn-primary btn-sm" id="addRuleBtn">
                    <i class="fa fa-plus me-1"></i>Yeni Kural Ekle
                </button>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-6 p-5">
                <i class="fa fa-info-circle fs-2 text-primary me-4 mt-1"></i>
                <div class="d-flex flex-stack flex-grow-1">
                    <div class="fw-semibold">
                        <div class="fs-6 text-gray-700">
                            Otomatik yanıt kuralları, belirli durumlarda müşteriye otomatik mesaj gönderir.
                            Örneğin: yeni destek talebi açıldığında hoş geldin mesajı, müşteri yanıt verdiğinde bilgilendirme mesajı vb.
                            <br><strong>Değişkenler:</strong> <code>{user_name}</code>, <code>{ticket_id}</code>, <code>{ticket_subject}</code>, <code>{department}</code>
                        </div>
                    </div>
                </div>
            </div>

            @forelse($autoReplies as $rule)
                <div class="card rule-card {{ $rule->is_active ? 'active' : 'inactive' }} mb-4 shadow-sm">
                    <div class="card-body py-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="me-5">
                                    @if($rule->is_active)
                                        <span class="badge badge-light-success trigger-badge"><i class="fa fa-check-circle me-1"></i>Aktif</span>
                                    @else
                                        <span class="badge badge-light-danger trigger-badge"><i class="fa fa-times-circle me-1"></i>Pasif</span>
                                    @endif
                                </div>
                                <div>
                                    <h5 class="fw-bold text-gray-800 mb-1">{{ $rule->name }}</h5>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge badge-light-info">
                                            <i class="fa fa-bolt me-1"></i>{{ \App\Models\SupportAutoReply::TRIGGER_EVENTS[$rule->trigger_event] ?? $rule->trigger_event }}
                                        </span>
                                        @if($rule->trigger_department)
                                            <span class="badge badge-light-warning">
                                                <i class="fa fa-building me-1"></i>{{ \App\Models\SupportAutoReply::DEPARTMENTS[$rule->trigger_department] ?? $rule->trigger_department }}
                                            </span>
                                        @else
                                            <span class="badge badge-light-secondary">
                                                <i class="fa fa-globe me-1"></i>Tüm Departmanlar
                                            </span>
                                        @endif
                                        @if(!empty($rule->trigger_product_category_ids))
                                            @foreach($rule->productCategories as $cat)
                                                <span class="badge badge-light-primary">
                                                    <i class="fa fa-box me-1"></i>{{ $cat->name }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="badge badge-light-secondary">
                                                <i class="fa fa-boxes me-1"></i>Tüm Ürün Grupları
                                            </span>
                                        @endif
                                        @if($rule->is_priority)
                                            <span class="badge badge-light-danger">
                                                <i class="fa fa-star me-1"></i>Öncelikli
                                            </span>
                                        @endif
                                        @if($rule->delay_minutes > 0)
                                            <span class="badge badge-light-primary">
                                                <i class="fa fa-clock me-1"></i>{{ $rule->delay_minutes }} dk gecikme
                                            </span>
                                        @else
                                            <span class="badge badge-light-success">
                                                <i class="fa fa-zap me-1"></i>Anında
                                            </span>
                                        @endif
                                        @if($rule->skip_if_admin_replied)
                                            <span class="badge badge-light-danger">
                                                <i class="fa fa-user-shield me-1"></i>Admin yanıtladıysa atla
                                            </span>
                                        @endif
                                        <span class="badge badge-light-dark">
                                            <i class="fa fa-sort me-1"></i>Sıra: {{ $rule->sort_order }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-light-success toggleStatusBtn"
                                        data-url="{{ route('admin.supports.autoReplies.toggleStatus', ['autoReply' => $rule->id]) }}"
                                        title="{{ $rule->is_active ? 'Pasif Yap' : 'Aktif Yap' }}">
                                    <i class="fa {{ $rule->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light-primary editRuleBtn"
                                        data-id="{{ $rule->id }}"
                                        data-name="{{ $rule->name }}"
                                        data-trigger-event="{{ $rule->trigger_event }}"
                                        data-trigger-department="{{ $rule->trigger_department }}"
                                        data-trigger-product-category-ids="{{ json_encode($rule->trigger_product_category_ids ?? []) }}"
                                        data-skip-if-admin-replied="{{ $rule->skip_if_admin_replied ? '1' : '0' }}"
                                        data-is-priority="{{ $rule->is_priority ? '1' : '0' }}"
                                        data-is-active="{{ $rule->is_active }}"
                                        data-sort-order="{{ $rule->sort_order }}"
                                        data-delay-minutes="{{ $rule->delay_minutes }}">
                                    <i class="fa fa-pen"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light-danger deleteRuleBtn"
                                        data-url="{{ route('admin.supports.autoReplies.delete', ['autoReply' => $rule->id]) }}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-10">
                    <i class="fa fa-robot fs-2x text-gray-300 mb-3 d-block"></i>
                    <p class="text-gray-500 fw-semibold fs-6">Henüz otomatik yanıt kuralı oluşturulmamış.</p>
                    <button type="button" class="btn btn-primary btn-sm mt-3" id="addRuleBtnEmpty">
                        <i class="fa fa-plus me-1"></i>İlk Kuralı Ekle
                    </button>
                </div>
            @endforelse
        </div>
    </div>

    <div class="modal fade" id="ruleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fw-bold" id="ruleModalTitle">Yeni Otomatik Yanıt Kuralı</h3>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal">
                        <i class="fa fa-times fs-4"></i>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-15">
                    <form id="ruleForm">
                        <input type="hidden" name="rule_id" id="ruleId" value="">

                        <div class="fv-row mb-5">
                            <label class="form-label fw-bold required">Kural Adı</label>
                            <input type="text" class="form-control" name="name" id="ruleName" placeholder="Örn: Hoş Geldin Mesajı" required />
                        </div>

                        <div class="row">
                            <div class="col-md-6 fv-row mb-5">
                                <label class="form-label fw-bold required">Tetikleme Olayı</label>
                                <select class="form-select" name="trigger_event" id="ruleTriggerEvent" required>
                                    @foreach(\App\Models\SupportAutoReply::TRIGGER_EVENTS as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 fv-row mb-5">
                                <label class="form-label fw-bold">Departman Filtresi</label>
                                <select class="form-select" name="trigger_department" id="ruleTriggerDepartment">
                                    @foreach(\App\Models\SupportAutoReply::DEPARTMENTS as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Boş bırakırsanız tüm departmanlar için geçerli olur.</small>
                            </div>
                        </div>

                        <div class="fv-row mb-5">
                            <label class="form-label fw-bold">Ürün Grubu Filtresi</label>
                            <select class="form-select" name="trigger_product_category_ids[]" id="ruleTriggerProductCategory" multiple size="4">
                                @foreach($productCategories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Birden fazla ürün grubu seçebilirsiniz. Boş bırakırsanız tüm ürün grupları için geçerli olur.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-4 fv-row mb-5">
                                <label class="form-label fw-bold">Gecikme (Dakika)</label>
                                <input type="number" class="form-control" name="delay_minutes" id="ruleDelayMinutes" value="0" min="0" />
                                <small class="text-muted">0 = anında gönderilir</small>
                            </div>
                            <div class="col-md-4 fv-row mb-5">
                                <label class="form-label fw-bold">Sıra Numarası</label>
                                <input type="number" class="form-control" name="sort_order" id="ruleSortOrder" value="0" />
                            </div>
                            <div class="col-md-4 fv-row mb-5">
                                <label class="form-label fw-bold">Durum</label>
                                <select class="form-select" name="is_active" id="ruleIsActive">
                                    <option value="1">Aktif</option>
                                    <option value="0">Pasif</option>
                                </select>
                            </div>
                        </div>

                        <div class="fv-row mb-5">
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="skip_if_admin_replied" id="ruleSkipIfAdminReplied" value="1" />
                                <label class="form-check-label fw-bold text-gray-700" for="ruleSkipIfAdminReplied">
                                    Admin daha önce yanıtladıysa tetikleme
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">Aktif edilirse, bir admin zaten destek talebine yanıt verdiyse bu kural çalışmaz.</small>
                        </div>

                        <div class="fv-row mb-5">
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="is_priority" id="ruleIsPriority" value="1" />
                                <label class="form-check-label fw-bold text-gray-700" for="ruleIsPriority">
                                    <i class="fa fa-star text-warning me-1"></i>Öncelikli Kural
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">Aktif edilirse, bu kural eşleştiğinde diğer otomatik mesajlar gönderilmez. Sadece bu kural çalışır.</small>
                        </div>

                        <div class="fv-row mb-5">
                            <label class="form-label fw-bold required">Mesaj İçeriği</label>
                            <div class="tiptap-wrapper">
                                <div class="tiptap-toolbar" id="autoReplyToolbar">
                                    <button type="button" data-action="bold" title="Kalın"><i class="fa fa-bold"></i></button>
                                    <button type="button" data-action="italic" title="İtalik"><i class="fa fa-italic"></i></button>
                                    <button type="button" data-action="underline" title="Altı Çizili"><i class="fa fa-underline"></i></button>
                                    <button type="button" data-action="strike" title="Üstü Çizili"><i class="fa fa-strikethrough"></i></button>
                                    <div class="separator"></div>
                                    <button type="button" data-action="heading2" title="Başlık 2">H2</button>
                                    <button type="button" data-action="heading3" title="Başlık 3">H3</button>
                                    <div class="separator"></div>
                                    <button type="button" data-action="bulletList" title="Liste"><i class="fa fa-list-ul"></i></button>
                                    <button type="button" data-action="orderedList" title="Numaralı Liste"><i class="fa fa-list-ol"></i></button>
                                    <div class="separator"></div>
                                    <button type="button" data-action="link" title="Link"><i class="fa fa-link"></i></button>
                                    <button type="button" data-action="image" title="Görsel"><i class="fa fa-image"></i></button>
                                    <div class="separator"></div>
                                    <button type="button" data-action="undo" title="Geri Al"><i class="fa fa-undo"></i></button>
                                    <button type="button" data-action="redo" title="İleri Al"><i class="fa fa-redo"></i></button>
                                </div>
                                <div class="tiptap-editor" id="autoReplyEditor"></div>
                            </div>
                            <small class="text-muted mt-1 d-block"><i class="fa fa-info-circle me-1"></i>Görselleri kopyala-yapıştır ile doğrudan editöre ekleyebilirsiniz.</small>
                            <div class="mt-3 p-3 bg-light-primary rounded">
                                <small class="fw-bold text-primary d-block mb-2"><i class="fa fa-code me-1"></i>Kullanılabilir Değişkenler</small>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge badge-light-primary cursor-pointer ar-variable-tag" data-var="{user_name}" title="Müşterinin adı soyadı"><code>{user_name}</code> Müşteri Adı</span>
                                    <span class="badge badge-light-primary cursor-pointer ar-variable-tag" data-var="{ticket_id}" title="Destek talebi numarası"><code>{ticket_id}</code> Talep No</span>
                                    <span class="badge badge-light-primary cursor-pointer ar-variable-tag" data-var="{ticket_subject}" title="Destek talebi konusu"><code>{ticket_subject}</code> Talep Konusu</span>
                                    <span class="badge badge-light-primary cursor-pointer ar-variable-tag" data-var="{department}" title="Departman adı"><code>{department}</code> Departman</span>
                                </div>
                                <small class="text-muted d-block mt-2">Değişkenlere tıklayarak editöre ekleyebilirsiniz.</small>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Vazgeç</button>
                            <button type="submit" class="btn btn-primary" id="ruleSubmitBtn">
                                <i class="fa fa-save me-1"></i>Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section("js")
    <script>
        var ruleMessages = {!! json_encode($autoReplies->pluck('message', 'id')->toArray()) !!};
        function decodeHtmlEntities(str) {
            if (!str) return str;
            var prev = '';
            while (prev !== str) {
                prev = str;
                var d = document.createElement('textarea');
                d.innerHTML = str;
                str = d.value;
            }
            return str;
        }
    </script>
    <script type="importmap">
    {
        "imports": {
            "@tiptap/core": "https://esm.sh/@tiptap/core@2.2.4",
            "@tiptap/starter-kit": "https://esm.sh/@tiptap/starter-kit@2.2.4",
            "@tiptap/extension-underline": "https://esm.sh/@tiptap/extension-underline@2.2.4",
            "@tiptap/extension-image": "https://esm.sh/@tiptap/extension-image@2.2.4",
            "@tiptap/extension-link": "https://esm.sh/@tiptap/extension-link@2.2.4",
            "@tiptap/extension-placeholder": "https://esm.sh/@tiptap/extension-placeholder@2.2.4"
        }
    }
    </script>
    <script type="module">
        import { Editor } from '@tiptap/core';
        import StarterKit from '@tiptap/starter-kit';
        import Underline from '@tiptap/extension-underline';
        import Image from '@tiptap/extension-image';
        import Link from '@tiptap/extension-link';
        import Placeholder from '@tiptap/extension-placeholder';

        let arEditor = null;

        function createEditor(content = '') {
            if (arEditor) { arEditor.destroy(); }
            arEditor = new Editor({
                element: document.querySelector('#autoReplyEditor'),
                extensions: [
                    StarterKit,
                    Underline,
                    Image.configure({ allowBase64: true }),
                    Link.configure({ openOnClick: false }),
                    Placeholder.configure({ placeholder: 'Otomatik yanıt mesajını buraya yazın...' }),
                ],
                content: content,
                editorProps: {
                    handlePaste(view, event) {
                        const items = (event.clipboardData || event.originalEvent.clipboardData).items;
                        for (const item of items) {
                            if (item.type.indexOf('image') === 0) {
                                event.preventDefault();
                                const blob = item.getAsFile();
                                const reader = new FileReader();
                                reader.onload = (e) => { arEditor.chain().focus().setImage({ src: e.target.result }).run(); };
                                reader.readAsDataURL(blob);
                                return true;
                            }
                        }
                        return false;
                    },
                    handleDrop(view, event) {
                        const files = event.dataTransfer?.files;
                        if (files && files.length) {
                            for (const file of files) {
                                if (file.type.indexOf('image') === 0) {
                                    event.preventDefault();
                                    const reader = new FileReader();
                                    reader.onload = (e) => { arEditor.chain().focus().setImage({ src: e.target.result }).run(); };
                                    reader.readAsDataURL(file);
                                    return true;
                                }
                            }
                        }
                        return false;
                    }
                }
            });

            document.querySelectorAll('#autoReplyToolbar button').forEach(btn => {
                btn.addEventListener('click', () => {
                    const action = btn.dataset.action;
                    switch (action) {
                        case 'bold': arEditor.chain().focus().toggleBold().run(); break;
                        case 'italic': arEditor.chain().focus().toggleItalic().run(); break;
                        case 'underline': arEditor.chain().focus().toggleUnderline().run(); break;
                        case 'strike': arEditor.chain().focus().toggleStrike().run(); break;
                        case 'heading2': arEditor.chain().focus().toggleHeading({ level: 2 }).run(); break;
                        case 'heading3': arEditor.chain().focus().toggleHeading({ level: 3 }).run(); break;
                        case 'bulletList': arEditor.chain().focus().toggleBulletList().run(); break;
                        case 'orderedList': arEditor.chain().focus().toggleOrderedList().run(); break;
                        case 'undo': arEditor.chain().focus().undo().run(); break;
                        case 'redo': arEditor.chain().focus().redo().run(); break;
                        case 'link':
                            const url = prompt('URL girin:');
                            if (url) arEditor.chain().focus().setLink({ href: url }).run();
                            break;
                        case 'image':
                            const imgUrl = prompt('Görsel URL girin:');
                            if (imgUrl) arEditor.chain().focus().setImage({ src: imgUrl }).run();
                            break;
                    }
                });
            });

            arEditor.on('transaction', () => {
                document.querySelectorAll('#autoReplyToolbar button[data-action]').forEach(btn => {
                    const action = btn.dataset.action;
                    btn.classList.remove('is-active');
                    if (['bold','italic','underline','strike','bulletList','orderedList'].includes(action)) {
                        if (arEditor.isActive(action)) btn.classList.add('is-active');
                    }
                });
            });
        }

        window._arCreateEditor = createEditor;
        window._arGetContent = () => arEditor ? arEditor.getHTML() : '';
        window._arInsertText = (text) => { if (arEditor) arEditor.chain().focus().insertContent(text).run(); };
    </script>

    <script>
        $(document).ready(function () {
            function openModal(isEdit, data) {
                if (isEdit && data) {
                    $("#ruleModalTitle").text("Kuralı Düzenle");
                    $("#ruleId").val(data.id);
                    $("#ruleName").val(data.name);
                    $("#ruleTriggerEvent").val(data.triggerEvent);
                    $("#ruleTriggerDepartment").val(data.triggerDepartment);
                    let catIds = data.triggerProductCategoryIds || [];
                    if (typeof catIds === 'string') { try { catIds = JSON.parse(catIds); } catch(e) { catIds = []; } }
                    $("#ruleTriggerProductCategory").val(catIds.map(String));
                    $("#ruleSkipIfAdminReplied").prop("checked", data.skipIfAdminReplied == '1');
                    $("#ruleIsPriority").prop("checked", data.isPriority == '1');
                    $("#ruleDelayMinutes").val(data.delayMinutes);
                    $("#ruleSortOrder").val(data.sortOrder);
                    $("#ruleIsActive").val(data.isActive);
                    setTimeout(() => { if (window._arCreateEditor) window._arCreateEditor(data.message); }, 100);
                } else {
                    $("#ruleModalTitle").text("Yeni Otomatik Yanıt Kuralı");
                    $("#ruleForm")[0].reset();
                    $("#ruleId").val("");
                    $("#ruleTriggerProductCategory").val([]);
                    $("#ruleSkipIfAdminReplied").prop("checked", false);
                    $("#ruleIsPriority").prop("checked", false);
                    setTimeout(() => { if (window._arCreateEditor) window._arCreateEditor(''); }, 100);
                }
                $("#ruleModal").modal("show");
            }

            $(document).on("click", "#addRuleBtn, #addRuleBtnEmpty", function () {
                openModal(false);
            });

            $(document).on("click", ".editRuleBtn", function () {
                let btn = $(this);
                let ruleId = btn.data("id");
                let rawMessage = ruleMessages[ruleId] || '';
                let decodedMessage = decodeHtmlEntities(rawMessage);
                openModal(true, {
                    id: ruleId,
                    name: btn.data("name"),
                    triggerEvent: btn.data("trigger-event"),
                    triggerDepartment: btn.data("trigger-department") || '',
                    triggerProductCategoryIds: btn.attr("data-trigger-product-category-ids") || '[]',
                    skipIfAdminReplied: btn.data("skip-if-admin-replied"),
                    isPriority: btn.data("is-priority"),
                    message: decodedMessage,
                    isActive: btn.data("is-active"),
                    sortOrder: btn.data("sort-order"),
                    delayMinutes: btn.data("delay-minutes")
                });
            });

            $(document).on("click", ".ar-variable-tag", function () {
                let varText = $(this).data("var");
                if (window._arInsertText) window._arInsertText(varText);
            });

            $(document).on("submit", "#ruleForm", function (e) {
                e.preventDefault();
                let ruleId = $("#ruleId").val();
                let isEdit = !!ruleId;
                let url = isEdit
                    ? "{{ route('admin.supports.autoReplies.store') }}".replace('/store', '/update/' + ruleId)
                    : "{{ route('admin.supports.autoReplies.store') }}";
                let messageContent = window._arGetContent ? window._arGetContent() : '';

                if (!messageContent || messageContent === '<p></p>') {
                    Swal.fire({ title: "Uyarı", text: "Mesaj içeriği boş olamaz.", icon: "warning" });
                    return;
                }

                let btn = $("#ruleSubmitBtn");
                let formData = {
                    _token: "{{ csrf_token() }}",
                    name: $("#ruleName").val(),
                    trigger_event: $("#ruleTriggerEvent").val(),
                    trigger_department: $("#ruleTriggerDepartment").val(),
                    skip_if_admin_replied: $("#ruleSkipIfAdminReplied").is(":checked") ? 1 : 0,
                    is_priority: $("#ruleIsPriority").is(":checked") ? 1 : 0,
                    message: messageContent,
                    is_active: $("#ruleIsActive").val(),
                    sort_order: $("#ruleSortOrder").val(),
                    delay_minutes: $("#ruleDelayMinutes").val()
                };
                let selectedCats = $("#ruleTriggerProductCategory").val();
                if (selectedCats && selectedCats.length > 0) {
                    formData['trigger_product_category_ids'] = selectedCats;
                }
                $.ajax({
                    type: "POST",
                    url: url,
                    dataType: "json",
                    data: formData,
                    beforeSend: function () { btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin me-1"></i>Kaydediliyor...'); },
                    complete: function (data) {
                        btn.prop("disabled", false).html('<i class="fa fa-save me-1"></i>Kaydet');
                        let res = data.responseJSON;
                        if (res && res.success === true) {
                            toastr.success(res.message);
                            setTimeout(() => window.location.reload(), 800);
                        } else {
                            Swal.fire({ title: "Hata", text: res?.message ?? "Bir hata oluştu.", icon: "error" });
                        }
                    }
                });
            });

            $(document).on("click", ".deleteRuleBtn", function () {
                let url = $(this).data("url");
                Swal.fire({
                    title: "Emin misiniz?",
                    text: "Bu otomatik yanıt kuralı silinecek.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Evet, Sil",
                    cancelButtonText: "Vazgeç",
                    confirmButtonColor: "#f1416c"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            dataType: "json",
                            data: { _token: "{{ csrf_token() }}" },
                            complete: function (data) {
                                let res = data.responseJSON;
                                if (res && res.success === true) {
                                    toastr.success(res.message);
                                    setTimeout(() => window.location.reload(), 800);
                                } else {
                                    Swal.fire({ title: "Hata", text: res?.message ?? "Bir hata oluştu.", icon: "error" });
                                }
                            }
                        });
                    }
                });
            });

            $(document).on("click", ".toggleStatusBtn", function () {
                let url = $(this).data("url");
                $.ajax({
                    type: "POST",
                    url: url,
                    dataType: "json",
                    data: { _token: "{{ csrf_token() }}" },
                    complete: function (data) {
                        let res = data.responseJSON;
                        if (res && res.success === true) {
                            toastr.success(res.message);
                            setTimeout(() => window.location.reload(), 800);
                        } else {
                            Swal.fire({ title: "Hata", text: res?.message ?? "Bir hata oluştu.", icon: "error" });
                        }
                    }
                });
            });
        });
    </script>
@endsection
