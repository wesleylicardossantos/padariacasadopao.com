@php
    $messages = [];
@endphp

@if($errors->any())
    <div class="flash-stack mb-3">
        <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show py-2 shadow-sm" role="alert">
            <div class="d-flex align-items-start gap-2">
                <i class="bx bx-x-circle mt-1"></i>
                <div>
                    <strong>Revise os dados informados.</strong>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    </div>
@endif
