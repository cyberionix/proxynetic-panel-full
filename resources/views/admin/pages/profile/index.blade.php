@extends("admin.template")
@section("title", "Profilim")
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
            padding: 4px 8px;
            cursor: pointer;
            font-size: 13px;
            color: #5e6278;
            transition: all 0.15s;
        }
        .tiptap-toolbar button:hover {
            background: #e4e6ef;
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
            min-height: 200px;
            max-height: 400px;
            overflow-y: auto;
        }
        .tiptap-editor .ProseMirror {
            padding: 12px 15px;
            min-height: 200px;
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
        .tiptap-editor .ProseMirror img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }
        .tiptap-editor .ProseMirror blockquote {
            border-left: 3px solid #009ef7;
            padding-left: 12px;
            margin-left: 0;
            color: #5e6278;
        }
        .tiptap-editor .ProseMirror a {
            color: #009ef7;
            text-decoration: underline;
        }
    </style>
@endsection
@section("breadcrumb")
    <x-admin.bread-crumb :data="['Profilim']"/>
@endsection
@section("description", "")
@section("keywords", "")
@section("master")
    <div class="row g-5">
        <div class="col-xl-4">
            <div class="card mb-5">
                <div class="card-body text-center pt-10 pb-10">
                    <div class="symbol symbol-100px symbol-circle mb-5">
                        <div class="symbol-label fs-1 bg-light-primary text-primary fw-bold text-uppercase">
                            {{ mb_substr($admin->first_name, 0, 1) }}{{ mb_substr($admin->last_name, 0, 1) }}
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1">{{ $admin->full_name }}</h3>
                    <p class="text-muted fs-6">{{ $admin->email }}</p>
                    @if($admin->phone)
                        <p class="text-muted fs-7"><i class="fa fa-phone me-1"></i>{{ $admin->phone }}</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-lock text-primary me-2"></i>Şifre Değiştir
                    </h3>
                </div>
                <div class="card-body">
                    <form id="passwordForm">
                        <div class="fv-row mb-5">
                            <label class="form-label fw-bold">Mevcut Şifre</label>
                            <input type="password" name="current_password" class="form-control" placeholder="••••••••" required />
                        </div>
                        <div class="fv-row mb-5">
                            <label class="form-label fw-bold">Yeni Şifre</label>
                            <input type="password" name="new_password" class="form-control" placeholder="••••••••" required />
                        </div>
                        <div class="fv-row mb-5">
                            <label class="form-label fw-bold">Yeni Şifre (Tekrar)</label>
                            <input type="password" name="new_password_confirmation" class="form-control" placeholder="••••••••" required />
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fa fa-save me-1"></i>Şifreyi Güncelle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card mb-5">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-user-edit text-primary me-2"></i>Profil Bilgileri
                    </h3>
                </div>
                <div class="card-body">
                    <form id="profileForm">
                        <div class="row">
                            <div class="col-md-6 fv-row mb-5">
                                <label class="form-label fw-bold">Ad</label>
                                <input type="text" name="first_name" class="form-control" value="{{ $admin->getRawOriginal('first_name') }}" required />
                            </div>
                            <div class="col-md-6 fv-row mb-5">
                                <label class="form-label fw-bold">Soyad</label>
                                <input type="text" name="last_name" class="form-control" value="{{ $admin->getRawOriginal('last_name') }}" required />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 fv-row mb-5">
                                <label class="form-label fw-bold">E-posta</label>
                                <input type="email" name="email" class="form-control" value="{{ $admin->email }}" required />
                            </div>
                            <div class="col-md-6 fv-row mb-5">
                                <label class="form-label fw-bold">Telefon</label>
                                <input type="text" name="phone" class="form-control" value="{{ $admin->phone }}" />
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fa fa-save me-1"></i>Profili Güncelle
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-signature text-primary me-2"></i>E-posta / Destek İmzası
                    </h3>
                    <div class="card-toolbar">
                        <span class="badge badge-light-info"><i class="fa fa-info-circle me-1"></i>Destek taleplerinde yanıt verirken otomatik eklenir</span>
                    </div>
                </div>
                <div class="card-body">
                    <form id="signatureForm">
                        <div class="fv-row mb-5">
                            <div class="tiptap-wrapper">
                                <div class="tiptap-toolbar" id="signatureToolbar">
                                    <button type="button" data-action="bold" title="Kalın"><i class="fa fa-bold"></i></button>
                                    <button type="button" data-action="italic" title="İtalik"><i class="fa fa-italic"></i></button>
                                    <button type="button" data-action="underline" title="Altı Çizili"><i class="fa fa-underline"></i></button>
                                    <button type="button" data-action="strike" title="Üstü Çizili"><i class="fa fa-strikethrough"></i></button>
                                    <div class="separator"></div>
                                    <button type="button" data-action="link" title="Link"><i class="fa fa-link"></i></button>
                                    <button type="button" data-action="image" title="Görsel"><i class="fa fa-image"></i></button>
                                    <div class="separator"></div>
                                    <button type="button" data-action="bulletList" title="Liste"><i class="fa fa-list-ul"></i></button>
                                    <button type="button" data-action="orderedList" title="Numaralı Liste"><i class="fa fa-list-ol"></i></button>
                                    <div class="separator"></div>
                                    <button type="button" data-action="undo" title="Geri Al"><i class="fa fa-undo"></i></button>
                                    <button type="button" data-action="redo" title="İleri Al"><i class="fa fa-redo"></i></button>
                                </div>
                                <div class="tiptap-editor" id="signatureEditor"></div>
                            </div>
                            <small class="text-muted mt-2 d-block"><i class="fa fa-info-circle me-1"></i>Görselleri kopyala-yapıştır ile doğrudan editöre ekleyebilirsiniz.</small>
                        </div>
                        <div class="mt-3 p-3 bg-light-primary rounded">
                            <small class="fw-bold text-primary d-block mb-2"><i class="fa fa-code me-1"></i>Kullanılabilir Değişkenler</small>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge badge-light-primary cursor-pointer sig-variable-tag" data-var="{admin_name}" title="Adminin adı soyadı"><code>{admin_name}</code> Admin Adı</span>
                                <span class="badge badge-light-primary cursor-pointer sig-variable-tag" data-var="{admin_email}" title="Admin e-posta adresi"><code>{admin_email}</code> Admin E-posta</span>
                                <span class="badge badge-light-primary cursor-pointer sig-variable-tag" data-var="{admin_phone}" title="Admin telefonu"><code>{admin_phone}</code> Admin Telefon</span>
                            </div>
                            <small class="text-muted d-block mt-2">Değişkenlere tıklayarak editöre ekleyebilirsiniz. Yanıt verirken otomatik olarak gerçek değerlerle değiştirilir.</small>
                        </div>
                        <div class="text-end mt-5">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fa fa-save me-1"></i>İmzayı Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if($admin->signature)
            <div class="card mt-5">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-eye text-success me-2"></i>İmza Önizleme
                    </h3>
                </div>
                <div class="card-body" id="signaturePreview">
                    {!! $admin->signature !!}
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection
@section("js")
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

        const signatureEditor = new Editor({
            element: document.querySelector('#signatureEditor'),
            extensions: [
                StarterKit,
                Underline,
                Image.configure({ allowBase64: true }),
                Link.configure({ openOnClick: false }),
                Placeholder.configure({ placeholder: 'İmzanızı buraya yazın... Örn: Saygılarımla, Ad Soyad' }),
            ],
            content: {!! json_encode($admin->signature ?? '') !!},
            editorProps: {
                handlePaste(view, event) {
                    const items = (event.clipboardData || event.originalEvent.clipboardData).items;
                    for (const item of items) {
                        if (item.type.indexOf('image') === 0) {
                            event.preventDefault();
                            const blob = item.getAsFile();
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                signatureEditor.chain().focus().setImage({ src: e.target.result }).run();
                            };
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
                                reader.onload = (e) => {
                                    signatureEditor.chain().focus().setImage({ src: e.target.result }).run();
                                };
                                reader.readAsDataURL(file);
                                return true;
                            }
                        }
                    }
                    return false;
                }
            }
        });

        window._signatureEditor = signatureEditor;
        window._signatureGetContent = () => signatureEditor.getHTML();
        window._signatureInsertText = (text) => signatureEditor.chain().focus().insertContent(text).run();

        document.querySelectorAll('#signatureToolbar button').forEach(btn => {
            btn.addEventListener('click', () => {
                const action = btn.dataset.action;
                switch (action) {
                    case 'bold': signatureEditor.chain().focus().toggleBold().run(); break;
                    case 'italic': signatureEditor.chain().focus().toggleItalic().run(); break;
                    case 'underline': signatureEditor.chain().focus().toggleUnderline().run(); break;
                    case 'strike': signatureEditor.chain().focus().toggleStrike().run(); break;
                    case 'bulletList': signatureEditor.chain().focus().toggleBulletList().run(); break;
                    case 'orderedList': signatureEditor.chain().focus().toggleOrderedList().run(); break;
                    case 'undo': signatureEditor.chain().focus().undo().run(); break;
                    case 'redo': signatureEditor.chain().focus().redo().run(); break;
                    case 'link':
                        const url = prompt('URL girin:');
                        if (url) signatureEditor.chain().focus().setLink({ href: url }).run();
                        break;
                    case 'image':
                        const imgUrl = prompt('Görsel URL girin:');
                        if (imgUrl) signatureEditor.chain().focus().setImage({ src: imgUrl }).run();
                        break;
                }
            });
        });

        signatureEditor.on('transaction', () => {
            document.querySelectorAll('#signatureToolbar button[data-action]').forEach(btn => {
                const action = btn.dataset.action;
                btn.classList.remove('is-active');
                if (['bold','italic','underline','strike','bulletList','orderedList'].includes(action)) {
                    if (signatureEditor.isActive(action)) btn.classList.add('is-active');
                }
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $(document).on("click", ".sig-variable-tag", function () {
                let varText = $(this).data("var");
                if (window._signatureInsertText) {
                    window._signatureInsertText(varText);
                }
            });

            $(document).on("submit", "#profileForm", function (e) {
                e.preventDefault();
                let form = $(this), btn = form.find("button[type='submit']");
                $.ajax({
                    type: "POST",
                    url: "{{ route('admin.profile.updateProfile') }}",
                    dataType: "json",
                    data: form.serialize() + "&_token={{ csrf_token() }}",
                    beforeSend: function () { btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin me-1"></i>Kaydediliyor...'); },
                    complete: function (data) {
                        btn.prop("disabled", false).html('<i class="fa fa-save me-1"></i>Profili Güncelle');
                        let res = data.responseJSON;
                        if (res && res.success === true) {
                            toastr.success(res.message);
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            Swal.fire({ title: "Hata", text: res?.message ?? "Bir hata oluştu.", icon: "error" });
                        }
                    }
                });
            });

            $(document).on("submit", "#passwordForm", function (e) {
                e.preventDefault();
                let form = $(this), btn = form.find("button[type='submit']");
                $.ajax({
                    type: "POST",
                    url: "{{ route('admin.profile.updatePassword') }}",
                    dataType: "json",
                    data: form.serialize() + "&_token={{ csrf_token() }}",
                    beforeSend: function () { btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin me-1"></i>Kaydediliyor...'); },
                    complete: function (data) {
                        btn.prop("disabled", false).html('<i class="fa fa-save me-1"></i>Şifreyi Güncelle');
                        let res = data.responseJSON;
                        if (res && res.success === true) {
                            toastr.success(res.message);
                            form[0].reset();
                        } else {
                            Swal.fire({ title: "Hata", text: res?.message ?? "Bir hata oluştu.", icon: "error" });
                        }
                    }
                });
            });

            $(document).on("submit", "#signatureForm", function (e) {
                e.preventDefault();
                let btn = $(this).find("button[type='submit']");
                let signatureContent = window._signatureGetContent ? window._signatureGetContent() : '';
                $.ajax({
                    type: "POST",
                    url: "{{ route('admin.profile.updateSignature') }}",
                    dataType: "json",
                    data: {
                        _token: "{{ csrf_token() }}",
                        signature: signatureContent
                    },
                    beforeSend: function () { btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin me-1"></i>Kaydediliyor...'); },
                    complete: function (data) {
                        btn.prop("disabled", false).html('<i class="fa fa-save me-1"></i>İmzayı Kaydet');
                        let res = data.responseJSON;
                        if (res && res.success === true) {
                            toastr.success(res.message);
                            if (document.getElementById('signaturePreview')) {
                                document.getElementById('signaturePreview').innerHTML = signatureContent;
                            } else {
                                window.location.reload();
                            }
                        } else {
                            Swal.fire({ title: "Hata", text: res?.message ?? "Bir hata oluştu.", icon: "error" });
                        }
                    }
                });
            });
        });
    </script>
@endsection
