@php
$menu = new App\Helpers\Menu();
$menu = $menu->preparaMenu();

$normalizePath = function ($url) {
    if (empty($url) || $url === 'javascript:;') {
        return '';
    }

    $path = parse_url($url, PHP_URL_PATH) ?: '';
    return trim($path, '/');
};

$isSubActive = function ($item) use ($normalizePath) {
    if (isset($item['rota_ativa']) || empty($item['rota'])) {
        return false;
    }

    $path = $normalizePath($item['rota']);
    if ($path === '') {
        return false;
    }

    if (!empty($item['exact_match'])) {
        return request()->path() === $path;
    }

    return request()->is($path) || request()->is($path . '/*');
};
@endphp

@foreach($menu as $m)
@if(!isset($m['ativo']) || $m['ativo'])
@php
    $subAtivo = false;
    foreach ($m['subs'] as $subMenu) {
        if ($isSubActive($subMenu)) {
            $subAtivo = true;
            break;
        }
    }

    $menuAtivo = ($m['titulo'] == ($rotaAtiva ?? null)) || $subAtivo;
@endphp
<li class="{{ $menuAtivo ? 'mm-active' : '' }}">
	<a href="javascript:;" class="has-arrow {{ $menuAtivo ? 'mm-active' : '' }}" aria-expanded="{{ $menuAtivo ? 'true' : 'false' }}">
		<div class="parent-icon"><i class='{{$m['icone']}}'></i>
		</div>
		<div class="menu-title">{{$m['titulo']}}</div>
	</a>
	<ul class="{{ $menuAtivo ? 'mm-show' : '' }}">	
		@foreach($m['subs'] as $i)
		@if(!isset($i['rota_ativa']) && $i['rota'] != '')
		@php($itemAtivo = $isSubActive($i))
		<li class="{{ $itemAtivo ? 'mm-active' : '' }}"><a class="{{ $itemAtivo ? 'mm-active' : '' }}" @isset($i['target']) target="_blank" @endisset href="{{$i['rota']}}" title="{{$i['nome']}}">
			<i class="bx bx-circle" style="font-size: 10px;"></i><span>{{$i['nome']}}</span></a>
		</li>
		@endif
		@endforeach
	</ul>
</li>
@endif
@endforeach
