<!-- IA AUTOMÁTICA WIDGET -->
<div id="ia-alertas-box" style="position:fixed; bottom:20px; right:20px; width:320px; z-index:9999;">
    <div style="background:#111; color:#fff; padding:12px; border-radius:10px;">
        <strong>IA da Empresa</strong>
        <ul id="ia-alertas-list" style="margin-top:10px; padding-left:15px;"></ul>
    </div>
</div>

<script>
fetch('/rh/ia-automatica')
.then(res => res.json())
.then(data => {
    let list = document.getElementById('ia-alertas-list');
    if (!list) return;
    list.innerHTML = '';
    if (!data.alertas || data.alertas.length === 0) {
        list.innerHTML = '<li>✔ Tudo sob controle</li>';
    } else {
        data.alertas.forEach(a => {
            let li = document.createElement('li');
            li.innerText = a;
            list.appendChild(li);
        });
    }
}).catch(() => {});
</script>
