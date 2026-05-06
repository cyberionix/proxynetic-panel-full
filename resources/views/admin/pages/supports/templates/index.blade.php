@extends("admin.template")
@section("title", "Hazır Mesaj Şablonları")
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
        .tiptap-toolbar button:hover {
            background: #e4e6ef;
            color: #181c32;
        }
        .tiptap-toolbar button.is-active {
            background: #009ef7;
            color: #fff;
            border-color: #009ef7;
        }
        .tiptap-toolbar .separator {
            width: 1px;
            background: #e4e6ef;
            margin: 0 4px;
        }
        .tiptap-editor {
            min-height: 250px;
            max-height: 400px;
            overflow-y: auto;
        }
        .tiptap-editor .ProseMirror {
            padding: 12px 15px;
            min-height: 250px;
            outline: none;
            font-size: 14px;
            color: #181c32;
            line-height: 1.7;
            cursor: text;
        }
        .tiptap-editor .ProseMirror:focus {
            outline: none;
        }
        .tiptap-editor .ProseMirror p.is-editor-empty:first-child::before {
            content: attr(data-placeholder);
            float: left;
            color: #b5b5c3;
            pointer-events: none;
            height: 0;
        }
        .tiptap-editor p {
            margin-bottom: 0.5rem;
        }
        .tiptap-editor img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            margin: 4px 0;
        }
        .tiptap-editor ul, .tiptap-editor ol {
            padding-left: 1.5rem;
        }
        .tiptap-editor blockquote {
            border-left: 3px solid #009ef7;
            padding-left: 12px;
            margin-left: 0;
            color: #7e8299;
        }
        .tiptap-editor h1, .tiptap-editor h2, .tiptap-editor h3 {
            margin-bottom: 0.5rem;
        }
        .tiptap-editor a {
            color: #009ef7;
            text-decoration: underline;
        }
    </style>
@endsection
@section("breadcrumb")
    <x-admin.bread-crumb :data="[__('support_tickets') => route('admin.supports.index'), 'Hazır Mesaj Şablonları']"/>
@endsection
@section("master")
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="fw-bold">Hazır Mesaj Şablonları</h3>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('admin.supports.autoReplies.index') }}" class="btn btn-light-warning btn-sm me-2">
                    <i class="fa fa-robot me-1"></i>Otomatik Yanıtlar
                </a>
                <button type="button" class="btn btn-primary btn-sm" id="addTemplateBtn">
                    <i class="fa fa-plus me-1"></i>Yeni Şablon Ekle
                </button>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="templatesTable">
                    <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-6 gs-0">
                        <th class="min-w-50px">#</th>
                        <th class="min-w-200px">Başlık</th>
                        <th class="min-w-100px">Durum</th>
                        <th class="min-w-100px">Sıra</th>
                        <th class="min-w-125px">{{__("action")}}</th>
                    </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                    @forelse($templates as $template)
                        <tr>
                            <td>{{ $template->id }}</td>
                            <td class="fw-bold">{{ $template->title }}</td>
                            <td>
                                @if($template->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger">Pasif</span>
                                @endif
                            </td>
                            <td>{{ $template->sort_order }}</td>
                            <td>
                                <button type="button"
                                        class="btn btn-light-primary btn-sm editTemplateBtn"
                                        data-id="{{ $template->id }}"
                                        data-title="{{ $template->title }}"
                                        data-content="{{ e($template->content) }}"
                                        data-is-active="{{ $template->is_active ? '1' : '0' }}"
                                        data-sort-order="{{ $template->sort_order }}">
                                    <i class="fa fa-edit"></i> {{__("edit")}}
                                </button>
                                <button type="button"
                                        class="btn btn-light-danger btn-sm deleteTemplateBtn"
                                        data-url="{{ route('admin.supports.templates.delete', ['template' => $template->id]) }}">
                                    <i class="fa fa-trash"></i> {{__("delete")}}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="text-gray-500 fw-bold text-center py-5 fs-6">Henüz şablon oluşturulmamış.</div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="templateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="templateModalTitle">Yeni Şablon Ekle</h2>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-15">
                    <form id="templateForm">
                        @csrf
                        <input type="hidden" name="template_id" id="templateId" value="">
                        <input type="hidden" name="content" id="templateContent" value="">
                        <div class="fv-row mb-5">
                            <label class="required form-label">Şablon Başlığı</label>
                            <input type="text" name="title" class="form-control" placeholder="Örn: Hoşgeldiniz Mesajı" required>
                        </div>
                        <div class="fv-row mb-5">
                            <label class="required form-label">Şablon İçeriği</label>
                            <div class="tiptap-wrapper">
                                <div class="tiptap-toolbar" id="tiptapToolbar">
                                    <button type="button" data-action="bold" title="Kalın"><i class="fa fa-bold"></i></button>
                                    <button type="button" data-action="italic" title="İtalik"><i class="fa fa-italic"></i></button>
                                    <button type="button" data-action="underline" title="Altı Çizili"><i class="fa fa-underline"></i></button>
                                    <button type="button" data-action="strike" title="Üstü Çizili"><i class="fa fa-strikethrough"></i></button>
                                    <div class="separator"></div>
                                    <button type="button" data-action="heading" data-level="2" title="Başlık 2"><i class="fa fa-heading"></i>2</button>
                                    <button type="button" data-action="heading" data-level="3" title="Başlık 3"><i class="fa fa-heading"></i>3</button>
                                    <div class="separator"></div>
                                    <button type="button" data-action="bulletList" title="Madde Listesi"><i class="fa fa-list-ul"></i></button>
                                    <button type="button" data-action="orderedList" title="Numaralı Liste"><i class="fa fa-list-ol"></i></button>
                                    <button type="button" data-action="blockquote" title="Alıntı"><i class="fa fa-quote-left"></i></button>
                                    <div class="separator"></div>
                                    <button type="button" data-action="link" title="Bağlantı"><i class="fa fa-link"></i></button>
                                    <button type="button" data-action="image" title="Görsel Ekle"><i class="fa fa-image"></i></button>
                                    <div class="separator"></div>
                                    <button type="button" data-action="horizontalRule" title="Yatay Çizgi"><i class="fa fa-minus"></i></button>
                                    <button type="button" data-action="undo" title="Geri Al"><i class="fa fa-undo"></i></button>
                                    <button type="button" data-action="redo" title="İleri Al"><i class="fa fa-redo"></i></button>
                                </div>
                                <div class="tiptap-editor" id="tiptapEditor"></div>
                            </div>
                            <small class="text-muted mt-1 d-block"><i class="fa fa-info-circle me-1"></i>Görselleri kopyala-yapıştır ile doğrudan editöre ekleyebilirsiniz.</small>
                            <div class="mt-3 p-3 bg-light-primary rounded">
                                <small class="fw-bold text-primary d-block mb-2"><i class="fa fa-code me-1"></i>Kullanılabilir Değişkenler</small>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge badge-light-primary cursor-pointer variable-tag" data-var="{user_name}" title="Müşterinin adı soyadı"><code>{user_name}</code> Müşteri Adı</span>
                                    <span class="badge badge-light-primary cursor-pointer variable-tag" data-var="{ticket_id}" title="Destek talebi numarası"><code>{ticket_id}</code> Talep No</span>
                                    <span class="badge badge-light-primary cursor-pointer variable-tag" data-var="{ticket_subject}" title="Destek talebi konusu"><code>{ticket_subject}</code> Talep Konusu</span>
                                </div>
                                <small class="text-muted d-block mt-2">Değişkenlere tıklayarak editöre ekleyebilir veya şablon içinde yazabilirsiniz. Yanıt verirken otomatik olarak gerçek değerlerle değiştirilir.</small>
                            </div>
                        </div>
                        <div class="row mb-5">
                            <div class="col-md-6">
                                <label class="form-label">Sıra Numarası</label>
                                <input type="number" name="sort_order" class="form-control" value="0" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Durum</label>
                                <select name="is_active" class="form-select">
                                    <option value="1">Aktif</option>
                                    <option value="0">Pasif</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex flex-center pt-5">
                            <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">{{__("cancel")}}</button>
                            <button type="submit" class="btn btn-primary" id="templateSubmitBtn">
                                <span class="indicator-label">{{__("save")}}</span>
                                <span class="indicator-progress">{{__("please_wait")}}...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section("js")
    <script type="importmap">
    {
        "imports": {
            "@tiptap/core": "https://esm.sh/@tiptap/core@2.6.6",
            "@tiptap/starter-kit": "https://esm.sh/@tiptap/starter-kit@2.6.6",
            "@tiptap/extension-image": "https://esm.sh/@tiptap/extension-image@2.6.6",
            "@tiptap/extension-link": "https://esm.sh/@tiptap/extension-link@2.6.6",
            "@tiptap/extension-underline": "https://esm.sh/@tiptap/extension-underline@2.6.6",
            "@tiptap/extension-placeholder": "https://esm.sh/@tiptap/extension-placeholder@2.6.6"
        }
    }
    </script>
    <script type="module">
        import { Editor } from '@tiptap/core';
        import StarterKit from '@tiptap/starter-kit';
        import Image from '@tiptap/extension-image';
        import Link from '@tiptap/extension-link';
        import Underline from '@tiptap/extension-underline';
        import Placeholder from '@tiptap/extension-placeholder';

        let editor = null;

        function createEditor(content = '') {
            if (editor) {
                editor.destroy();
            }

            editor = new Editor({
                element: document.querySelector('#tiptapEditor'),
                extensions: [
                    StarterKit,
                    Underline,
                    Image.configure({
                        inline: true,
                        allowBase64: true,
                    }),
                    Link.configure({
                        openOnClick: false,
                    }),
                    Placeholder.configure({
                        placeholder: 'Şablon içeriğini buraya yazın veya görsel yapıştırın...',
                    }),
                ],
                content: content,
                editorProps: {
                    handlePaste(view, event) {
                        const items = (event.clipboardData || event.originalEvent.clipboardData).items;
                        let imageHandled = false;

                        for (const item of items) {
                            if (item.type.indexOf('image') === 0) {
                                event.preventDefault();
                                const file = item.getAsFile();
                                const reader = new FileReader();
                                reader.onload = function (e) {
                                    editor.chain().focus().setImage({ src: e.target.result }).run();
                                };
                                reader.readAsDataURL(file);
                                imageHandled = true;
                            }
                        }
                        return imageHandled;
                    },
                    handleDrop(view, event) {
                        const files = event.dataTransfer?.files;
                        if (files && files.length > 0) {
                            for (const file of files) {
                                if (file.type.indexOf('image') === 0) {
                                    event.preventDefault();
                                    const reader = new FileReader();
                                    reader.onload = function (e) {
                                        editor.chain().focus().setImage({ src: e.target.result }).run();
                                    };
                                    reader.readAsDataURL(file);
                                    return true;
                                }
                            }
                        }
                        return false;
                    },
                },
                onUpdate({ editor: ed }) {
                    updateToolbarState(ed);
                },
                onSelectionUpdate({ editor: ed }) {
                    updateToolbarState(ed);
                },
            });
        }

        function updateToolbarState(ed) {
            document.querySelectorAll('#tiptapToolbar button[data-action]').forEach(btn => {
                const action = btn.dataset.action;
                let isActive = false;
                if (action === 'heading') {
                    isActive = ed.isActive('heading', { level: parseInt(btn.dataset.level) });
                } else if (action === 'bulletList') {
                    isActive = ed.isActive('bulletList');
                } else if (action === 'orderedList') {
                    isActive = ed.isActive('orderedList');
                } else if (['bold', 'italic', 'underline', 'strike', 'blockquote', 'link'].includes(action)) {
                    isActive = ed.isActive(action);
                }
                btn.classList.toggle('is-active', isActive);
            });
        }

        document.querySelector('#tiptapToolbar').addEventListener('click', function (e) {
            const btn = e.target.closest('button[data-action]');
            if (!btn || !editor) return;
            e.preventDefault();
            const action = btn.dataset.action;

            switch (action) {
                case 'bold': editor.chain().focus().toggleBold().run(); break;
                case 'italic': editor.chain().focus().toggleItalic().run(); break;
                case 'underline': editor.chain().focus().toggleUnderline().run(); break;
                case 'strike': editor.chain().focus().toggleStrike().run(); break;
                case 'heading':
                    editor.chain().focus().toggleHeading({ level: parseInt(btn.dataset.level) }).run();
                    break;
                case 'bulletList': editor.chain().focus().toggleBulletList().run(); break;
                case 'orderedList': editor.chain().focus().toggleOrderedList().run(); break;
                case 'blockquote': editor.chain().focus().toggleBlockquote().run(); break;
                case 'horizontalRule': editor.chain().focus().setHorizontalRule().run(); break;
                case 'undo': editor.chain().focus().undo().run(); break;
                case 'redo': editor.chain().focus().redo().run(); break;
                case 'link': {
                    const prevUrl = editor.getAttributes('link').href;
                    const url = window.prompt('Bağlantı URL:', prevUrl || 'https://');
                    if (url === null) return;
                    if (url === '') {
                        editor.chain().focus().extendMarkRange('link').unsetLink().run();
                    } else {
                        editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
                    }
                    break;
                }
                case 'image': {
                    const input = document.createElement('input');
                    input.type = 'file';
                    input.accept = 'image/*';
                    input.onchange = function () {
                        const file = input.files[0];
                        if (!file) return;
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            editor.chain().focus().setImage({ src: e.target.result }).run();
                        };
                        reader.readAsDataURL(file);
                    };
                    input.click();
                    break;
                }
            }
        });

        window._tiptapCreateEditor = createEditor;
        window._tiptapGetContent = function () {
            return editor ? editor.getHTML() : '';
        };
        window._tiptapInsertText = function (text) {
            if (editor) {
                editor.chain().focus().insertContent(text).run();
            }
        };
    </script>
    <script>
        $(document).ready(function () {
            $("#addTemplateBtn").on("click", function () {
                $("#templateModalTitle").text("Yeni Şablon Ekle");
                $("#templateId").val("");
                $("#templateForm")[0].reset();
                $("#templateForm [name='is_active']").val("1");
                $("#tiptapEditor").html("");
                $("#templateModal").modal("show");
                setTimeout(function () { window._tiptapCreateEditor(''); }, 300);
            });

            $(document).on("click", ".variable-tag", function () {
                let varText = $(this).data("var");
                if (window._tiptapInsertText) {
                    window._tiptapInsertText(varText);
                }
            });

            $(document).on("click", ".editTemplateBtn", function () {
                let btn = $(this);
                $("#templateModalTitle").text("Şablonu Düzenle");
                $("#templateId").val(btn.data("id"));
                $("#templateForm [name='title']").val(btn.data("title"));
                $("#templateForm [name='sort_order']").val(btn.data("sort-order"));
                $("#templateForm [name='is_active']").val(btn.data("is-active").toString());
                let decoded = $('<textarea/>').html(btn.data("content")).text();
                $("#tiptapEditor").html("");
                $("#templateModal").modal("show");
                setTimeout(function () { window._tiptapCreateEditor(decoded); }, 300);
            });

            $(document).on("submit", "#templateForm", function (e) {
                e.preventDefault();
                let templateId = $("#templateId").val(),
                    url = templateId
                        ? "{{ route('admin.supports.templates.update', ['template' => '__ID__']) }}".replace('__ID__', templateId)
                        : "{{ route('admin.supports.templates.store') }}";

                let content = window._tiptapGetContent ? window._tiptapGetContent() : '';
                $("#templateContent").val(content);

                let formData = new FormData(this);

                $.ajax({
                    type: "POST",
                    url: url,
                    data: formData,
                    dataType: "json",
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        propSubmitButton($("#templateSubmitBtn"), 1);
                    },
                    complete: function (data) {
                        propSubmitButton($("#templateSubmitBtn"), 0);
                        let res = data.responseJSON;
                        if (res && res.success === true) {
                            Swal.fire({
                                title: "{{__('success')}}",
                                text: res.message,
                                icon: "success",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}"
                            }).then(() => window.location.reload());
                        } else {
                            Swal.fire({
                                title: "{{__('error')}}",
                                text: res?.message ?? "{{__('form_has_errors')}}",
                                icon: "error",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}"
                            });
                        }
                    }
                });
            });

            $(document).on("click", ".deleteTemplateBtn", function () {
                let url = $(this).data("url");
                Swal.fire({
                    icon: "warning",
                    title: "{{__('warning')}}",
                    text: "Bu şablonu silmek istediğinize emin misiniz?",
                    showConfirmButton: true,
                    showCancelButton: true,
                    cancelButtonText: "{{__('close')}}",
                    confirmButtonText: "{{__('yes')}}"
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
                                    Swal.fire({
                                        title: "{{__('success')}}",
                                        text: res.message,
                                        icon: "success",
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{__('close')}}"
                                    }).then(() => window.location.reload());
                                } else {
                                    Swal.fire({
                                        title: "{{__('error')}}",
                                        text: res?.message ?? "{{__('form_has_errors')}}",
                                        icon: "error",
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{__('close')}}"
                                    });
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
