@php
    $branding = $branding ?? ($empresaBranding ?? []);
    $logoUrl = $empresa->branding_logo_url ?? ($branding['logo_url'] ?? asset('logos/default.png'));
    $backgroundUrl = $empresa->branding_background_url ?? ($branding['background_url'] ?? asset('assets/images/img123.jpg'));
@endphp

<div class="row">
    <div class="col-lg-6 mb-4">
        <label class="form-label fw-bold">Logo da empresa</label>
        <div class="border rounded p-3 bg-light">
            <div class="mb-3 text-center">
                <img src="{{ $logoUrl }}" id="logo-preview-current" class="img-fluid rounded bg-white p-3 border" style="max-height: 120px; object-fit: contain;" onerror="this.onerror=null;this.src='{{ asset('logos/default.png') }}';">
            </div>
            <input type="file" name="logo" id="logo-upload" class="form-control" accept="image/*">
            <small class="text-muted d-block mt-2">PNG, JPG, WEBP ou SVG. Recomendado: fundo transparente e largura mínima de 240px.</small>
            @error('logo')<div class="text-danger mt-2">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <label class="form-label fw-bold">Imagem de fundo do login</label>
        <div class="border rounded p-3 bg-light">
            <div class="mb-3 text-center">
                <img src="{{ $backgroundUrl }}" id="background-preview-current" class="img-fluid rounded border" style="width: 100%; max-height: 220px; object-fit: cover;">
            </div>
            <input type="file" name="background_image" id="background-upload" class="form-control" accept="image/*">
            <small class="text-muted d-block mt-2">PNG, JPG ou WEBP. Recomendado: 1600x900 ou maior.</small>
            @error('background_image')<div class="text-danger mt-2">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="row align-items-end g-3 mt-1">
    <div class="col-lg-6">
        <div class="alert alert-info mb-0">
            <strong>Empresa:</strong> {{ $empresa->nome_fantasia ?: $empresa->razao_social }}<br>
            <strong>Hash:</strong> {{ $empresa->hash ?: 'não definido' }}
        </div>
    </div>
    <div class="col-lg-6">
        <div class="text-lg-end text-start pb-2">
            <button type="submit" class="btn btn-primary px-4 py-3 w-100 w-lg-auto" style="min-width: 240px; white-space: normal; line-height: 1.2;">
                Salvar identidade visual
            </button>
        </div>
    </div>
</div>

@section('js')
<script>
    function previewImage(input, previewId) {
        if (!input || !input.files || !input.files[0]) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            const preview = document.getElementById(previewId);
            if (preview) preview.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }

    document.getElementById('logo-upload')?.addEventListener('change', function() {
        previewImage(this, 'logo-preview-current');
    });

    document.getElementById('background-upload')?.addEventListener('change', function() {
        previewImage(this, 'background-preview-current');
    });
</script>
@endsection
