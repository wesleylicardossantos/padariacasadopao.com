VENDAS = []
let dashboardChart1 = null
let dreChart = null
let biDailyChart = null
let paymentChart = null

$(function(){
	setTimeout(() => {
		getDataCards()
		renderGraficoVendasAnual()
		graficoContaPagar()
		graficoContaReceber()
		loadDreResumo()
		loadBiResumo()
		loadPdvAuditoria()
	}, 100)
})

$('#locais').change(() => {
	getDataCards()
	renderGraficoVendasAnual()
	graficoContaPagar()
	graficoContaReceber()
	loadDreResumo()
	loadBiResumo()
	loadPdvAuditoria()
})

function getDataCards(){
	let empresa_id = $('#empresa_id').val();
	let local_id = $('#locais').val();

	$.get(path_url + 'api/graficos/getDataCards', {
		local_id: local_id,
		empresa_id: empresa_id
	}).done((success) => {
		if(success){
			$('.total_vendas').text("R$ " + convertFloatToMoeda(success.vendas_historico || success.vendas || 0))
			$('.total_vendas_mes').text("R$ " + convertFloatToMoeda(success.vendas_mes || 0))
			$('.total_vendas_hoje').text("R$ " + convertFloatToMoeda(success.vendas_hoje || 0))
			$('.total_produtos').text(success.produtos || 0)
			$('.total_pagar').text("R$ " + convertFloatToMoeda(success.conta_pagar || 0))
			$('.total_receber').text("R$ " + convertFloatToMoeda(success.conta_receber || 0))
			$('.total_ticket_medio').text("R$ " + convertFloatToMoeda(success.ticket_medio || 0))
			$('.total_qtd_vendas').text((success.qtd_vendas || 0))
			$('.total_saldo_previsto').text("R$ " + convertFloatToMoeda(success.saldo_previsto || 0))
			renderFinancialAudit(success.audit || null)
		}
	})
	.fail((err) => {
		console.log(err)
	})
}


function getAuditoriaFinanceira(call){
	let empresa_id = $('#empresa_id').val();
	let local_id = $('#locais').val();
	$.get(path_url + 'api/graficos/auditoriaFinanceira', {empresa_id: empresa_id, local_id: local_id})
	.done((success) => call(success))
	.fail((err) => {
		console.log(err)
		call(null)
	})
}

function renderFinancialAudit(audit){
	if(!audit){
		getAuditoriaFinanceira((res) => {
			if(res){
				renderFinancialAudit(res)
			}
		})
		return
	}

	const money = (value) => 'R$ ' + convertFloatToMoeda(value || 0)
	const percent = (value) => (parseFloat(value || 0).toFixed(2).replace('.', ',')) + '%'
	const status = audit.status_fluxo || 'saudavel'
	const statusLabel = audit.status_fluxo_label || 'Saudável'
	const saldoProjetado = parseFloat(audit.saldo_projetado || 0)
	const diferenca = parseFloat(audit.diferenca_faturado_recebido || 0)

	$('.audit-status')
		.removeClass('saudavel atencao critico')
		.addClass(status)
		.text('Status: ' + statusLabel)

	$('.audit-updated-at').text('Atualizado em ' + (audit.atualizado_em || '--'))
	$('.audit-percentual-recebido').text(percent(audit.percentual_recebido_faturado))
	$('.audit-percentual-pago').text(percent(audit.percentual_pago_obrigacoes))
	$('.audit-saldo-projetado').text(money(saldoProjetado))
	$('.audit-diferenca-faturado')
		.text(money(diferenca))
		.removeClass('audit-diff-positive audit-diff-negative')
		.addClass(diferenca > 0 ? 'audit-diff-positive' : 'audit-diff-negative')
	$('.audit-faturamento-venda').text(money(audit.faturamento_venda))
	$('.audit-faturamento-caixa').text(money(audit.faturamento_venda_caixa))
	$('.audit-faturamento-total').text(money(audit.faturamento_total_mes))
	$('.audit-recebido-mes').text(money(audit.recebido_no_mes))
	$('.audit-pago-mes').text(money(audit.pago_no_mes))
	$('.audit-receber-aberto').text(money(audit.contas_receber_aberto))
	$('.audit-pagar-aberto').text(money(audit.contas_pagar_aberto))

	let leitura = 'Saldo estável e operação equilibrada.'
	if(status === 'critico'){
		leitura = 'Ação imediata: contas a pagar ou diferença de caixa exigem intervenção.'
	}else if(status === 'atencao'){
		leitura = 'Monitorar recebimentos e compromissos para evitar pressão de caixa.'
	}
	$('.audit-leitura').text(leitura)
}

$('#set-location').click(() => {
	let filial_id = $('#locais').val()
	$.get(path_url + 'usuarios/set-location', {filial_id: filial_id})
	.done((success) => {
		console.log(success)
		swal("Sucesso", "Local definido como padrão!", "success")

	})
	.fail((err) => {
		console.log(err)
		swal("Opss", "Algo deu errado!", "error")
	})
})

$('#filial_id').change(() => {
	setTimeout(() => {
		$('#seteDias').trigger('click')
		filtrar()
		buscaProdutos()
	}, 10)
})


function getVendasAnual(call){
	let empresa_id = $('#empresa_id').val();
	let local_id = $('#locais').val();
	$.get(path_url + 'api/graficos/vendasAnual', {empresa_id: empresa_id, local_id: local_id})
	.done((success) => {
		call(success)
	}).fail((err) => {
		console.log(err)
		call([])

	})
}

function getProdutos(call){
	let empresa_id = $('#empresa_id').val();
	let filial_id = $('#locais').val()
	$.get(path_url + 'api/graficos/produtos', {empresa_id: empresa_id, filial_id: filial_id})
	.done((success) => {
		console.log(success)
		call(success)
	}).fail((err) => {
		console.log(err)
		call([])

	})
}

function getContasReceber(call){
	let empresa_id = $('#empresa_id').val();
	let filial_id = $('#locais').val()

	$.get(path_url + 'api/graficos/contasReceber', {empresa_id: empresa_id, filial_id: filial_id})
	.done((success) => {
		console.log(success)
		call(success)
	}).fail((err) => {
		console.log(err)
		call([])

	})
}

function getContasPagar(call){

	let empresa_id = $('#empresa_id').val();
	let filial_id = $('#locais').val()

	$.get(path_url + 'api/graficos/contasPagar', {empresa_id: empresa_id, filial_id: filial_id})
	.done((success) => {
		console.log(success)
		call(success)
	}).fail((err) => {
		console.log(err)
		call([])

	})
}

function filtroBox(dias){
	let empresa_id = $('#empresa_id').val();

	$.get(path_url + 'api/graficos/boxConsulta', {dias: dias, empresa_id: empresa_id})
	.done((success) => {

		console.log(success)
		// $('.total_vendas').text("R$ " + success.totalDeVendas)
		// $('.total_receber').text("R$ " + success.totalDeContaReceber)
		// $('.total_pagar').text("R$ " + success.totalDeContaPagar)
	})
	.fail((err) => {
	})
}

function renderGraficoVendasAnual(){
	getVendasAnual((res) => {
		var options = {
			series: [{
				name: 'Valor',
				data: res.somaVendas || []
			}],
			chart: {
				foreColor: '#9a9797',
				type: 'area',
				height: 380,
				zoom: { enabled: false },
				toolbar: { show: false },
				dropShadow: { enabled: false, top: 3, left: 14, blur: 4, opacity: 0.10 }
			},
			stroke: { width: 4, curve: 'smooth' },
			xaxis: { categories: res.meses || [] },
			dataLabels: { enabled: false },
			fill: {
				type: 'gradient',
				gradient: {
					shade: 'light',
					gradientToColors: ['#8833ff'],
					shadeIntensity: 1,
					type: 'vertical',
					opacityFrom: 0.8,
					opacityTo: 0.3,
				},
			},
			colors: ['#8833ff'],
			yaxis: {
				labels: { formatter: function (value) { return 'R$ ' + value; } },
			},
			markers: {
				size: 4,
				colors: ['#8833ff'],
				strokeColors: '#fff',
				strokeWidth: 2,
				hover: { size: 7 }
			},
			grid: { show: true, borderColor: '#ededed', strokeDashArray: 4 }
		};

		if (dashboardChart1) {
			dashboardChart1.updateOptions({
				xaxis: { categories: res.meses || [] },
				series: [{ name: 'Valor', data: res.somaVendas || [] }]
			}, true, true)
			return
		}

		dashboardChart1 = new ApexCharts(document.querySelector('#chart1'), options)
		dashboardChart1.render()
	})
}

$(function () {
	"use strict";

	setTimeout(() => {
		// filtroBox(7)
	},20)

	// chart1


	//grafico de produtos
	// chart 2
	getProdutos((res) => {
		var options = {
			series: [{
				name: 'Cadastrado no mês',
				data: res.somaCadastradoMes
			}, {
				name: 'Vendidos no mês',
				data: res.somaVendidosNoDia
			}, {
				name: 'Sem venda no mês',
				data: res.somaNaoVendidos
			}],
			chart: {
				foreColor: '#9a9797',
				type: 'bar',
				height: 320,
				stacked: true,
				toolbar: {
					show: false
				},
			},
			plotOptions: {
				bar: {
					horizontal: false,
					columnWidth: '18%',
				},
			},
			legend: {
				show: false,
				position: 'top',
				horizontalAlign: 'left',
				offsetX: -20
			},
			dataLabels: {
				enabled: false
			},
			stroke: {
				show: true,
				width: 2,
				colors: ['transparent']
			},
			colors: ["#e62e2e", "#29cc39", "#0dcaf0"],
			xaxis: {
				categories: res.meses,
			},
			fill: {
				opacity: 1
			},
			grid: {
				show: true,
				borderColor: '#ededed',
				strokeDashArray: 4,
			},
			responsive: [{
				breakpoint: 480,
				options: {
					chart: {
						height: 310,
					},
					plotOptions: {
						bar: {
							columnWidth: '30%'
						}
					}
				}
			}]
		};
		var chart = new ApexCharts(document.querySelector("#chart2"), options);
		chart.render();
	});
	
});

function graficoContaReceber(){
	getContasReceber((res) => {
		console.log(res)

		$('.cr-recebido').text("R$"+res.recebidas)
		$('.cr-receber').text("R$"+res.receber)
		var options = {
			series: [res.percentual],
			chart: {
				height: 380,
				type: 'radialBar',
				offsetY: -10
			},
			plotOptions: {
				radialBar: {
					startAngle: -135,
					endAngle: 135,
					hollow: {
						margin: 0,
						size: '70%',
						background: 'transparent',
					},
					track: {
						strokeWidth: '100%',
						dropShadow: {
							enabled: false,
							top: -3,
							left: 0,
							blur: 4,
							opacity: 0.12
						}
					},
					dataLabels: {
						name: {
							fontSize: '16px',
							color: '#212529',
							offsetY: 5
						},
						value: {
							offsetY: 20,
							fontSize: '30px',
							color: '#212529',
							formatter: function (val) {
								return val + "%";
							}
						}
					}
				}
			},
			fill: {
				type: 'gradient',
				gradient: {
					shade: 'dark',
					shadeIntensity: 0.15,
					gradientToColors: ['#4a00e0'],
					inverseColors: false,
					opacityFrom: 1,
					opacityTo: 1,
					stops: [0, 50, 65, 91]
				},
			},
			colors: ["#8e2de2"],
			stroke: {
				dashArray: 4
			},
			labels: ['Recebido'],
			responsive: [{
				breakpoint: 480,
				options: {
					chart: {
						height: 300,
					}
				}
			}]
		};
		var chart = new ApexCharts(document.querySelector("#chart4"), options);
		chart.render();
	})
}

function graficoContaPagar(){
	getContasPagar((res) => {
		console.log(res)

		$('.cp-pago').text("R$"+res.pagos)
		$('.cp-pagar').text("R$"+res.pagar)
		var options = {
			series: [res.percentual],
			chart: {
				height: 380,
				type: 'radialBar',
				offsetY: -10
			},
			plotOptions: {
				radialBar: {
					startAngle: -135,
					endAngle: 135,
					hollow: {
						margin: 0,
						size: '70%',
						background: 'transparent',
					},
					track: {
						strokeWidth: '100%',
						dropShadow: {
							enabled: false,
							top: -3,
							left: 0,
							blur: 4,
							opacity: 0.12
						}
					},
					dataLabels: {
						name: {
							fontSize: '16px',
							color: '#212529',
							offsetY: 5
						},
						value: {
							offsetY: 20,
							fontSize: '30px',
							color: '#212529',
							formatter: function (val) {
								return val + "%";
							}
						}
					}
				}
			},
			fill: {
				type: 'gradient',
				gradient: {
					shade: 'dark',
					shadeIntensity: 0.15,
					gradientToColors: ['#4a00e0'],
					inverseColors: false,
					opacityFrom: 1,
					opacityTo: 1,
					stops: [0, 50, 65, 91]
				},
			},
			colors: ["#8e2de2"],
			stroke: {
				dashArray: 4
			},
			labels: ['Pago'],
			responsive: [{
				breakpoint: 480,
				options: {
					chart: {
						height: 300,
					}
				}
			}]
		};
		var chart = new ApexCharts(document.querySelector("#chart9"), options);
		chart.render();
	});
}




function getDreResumo(call){
	let empresa_id = $('#empresa_id').val();
	let local_id = $('#locais').val();
	$.get(path_url + 'api/graficos/dreResumo', {empresa_id: empresa_id, local_id: local_id})
	.done((success) => call(success))
	.fail((err) => { console.log(err); call(null) })
}

function getBiResumo(call){
	let empresa_id = $('#empresa_id').val();
	let local_id = $('#locais').val();
	$.get(path_url + 'api/graficos/biResumo', {empresa_id: empresa_id, local_id: local_id})
	.done((success) => call(success))
	.fail((err) => { console.log(err); call(null) })
}

function getAuditoriaPdv(call){
	let empresa_id = $('#empresa_id').val();
	let local_id = $('#locais').val();
	$.get(path_url + 'api/graficos/auditoriaPdv', {empresa_id: empresa_id, local_id: local_id})
	.done((success) => call(success))
	.fail((err) => { console.log(err); call(null) })
}

function loadDreResumo(){
	getDreResumo((res) => {
		if(!res) return
		const money = (value) => 'R$ ' + convertFloatToMoeda(value || 0)
		const percent = (value) => (parseFloat(value || 0).toFixed(2).replace('.', ',')) + '%'
		$('.dre-periodo').text(res.periodo || '--')
		$('.dre-status').removeClass('lucro prejuizo atencao').addClass(res.status_resultado || 'lucro').text(res.status_resultado_label || 'Lucro real positivo')
		$('.dre-receita-bruta').text(money(res.receita_bruta))
		$('.dre-deducoes').text(money(res.deducoes))
		$('.dre-receita-liquida').text(money(res.receita_liquida))
		$('.dre-custos-variaveis').text(money(res.custos_variaveis))
		$('.dre-despesas-fixas').text(money(res.despesas_fixas))
		$('.dre-lucro-bruto').text(money(res.lucro_bruto))
		$('.dre-lucro-liquido').text(money(res.lucro_liquido))
		$('.dre-margem-liquida').text(percent(res.margem_liquida_percentual))
		$('.dre-markup').text(percent(res.markup_percentual))
		$('.dre-ponto-equilibrio').text(percent(res.ponto_equilibrio_percentual_receita))
		$('.dre-fonte-custo').text(res.fonte_custo || '--')
	})
}

function loadBiResumo(){
	getBiResumo((res) => {
		if(!res) return
		renderBiDailyChart(res.serie_diaria || null)
		renderBiPaymentChart(res.formas_pagamento || null)
		renderDreYearChart(res.dre_anual || null)
		renderRankingTable('.bi-top-produtos', res.top_produtos || [], 'produto')
		renderRankingTable('.bi-top-clientes', res.top_clientes || [], 'cliente')
		$('.bi-periodo').text(res.periodo || '--')
		$('.bi-receita-liquida').text('R$ ' + convertFloatToMoeda(res.resumo ? res.resumo.receita_liquida : 0))
		$('.bi-lucro-liquido').text('R$ ' + convertFloatToMoeda(res.resumo ? res.resumo.lucro_liquido : 0))
		$('.bi-margem-liquida').text((parseFloat(res.resumo ? res.resumo.margem_liquida_percentual : 0).toFixed(2).replace('.', ',')) + '%')
		$('.bi-updated-at').text('Atualizado em ' + (res.atualizado_em || '--'))
	})
}

function loadPdvAuditoria(){
	getAuditoriaPdv((res) => {
		if(!res) return
		const money = (value) => 'R$ ' + convertFloatToMoeda(value || 0)
		$('.pdv-audit-status').removeClass('saudavel atencao critico').addClass(res.status || 'saudavel').text(res.status_label || 'Sem divergências relevantes')
		$('.pdv-audit-periodo').text(res.periodo || '--')
		$('.pdv-total-vendas').text(money(res.total_vendas_pdv))
		$('.pdv-total-financeiro').text(money(res.total_financeiro_pdv))
		$('.pdv-diferenca-total').text(money(res.diferenca_total))
		$('.pdv-qtd-vendas').text(res.quantidade_vendas_pdv || 0)
		$('.pdv-sem-conta').text(res.vendas_sem_conta || 0)
		$('.pdv-com-divergencia').text(res.vendas_com_divergencia || 0)
		$('.pdv-updated-at').text('Atualizado em ' + (res.atualizado_em || '--'))
		renderPdvAuditTable(res.itens || [])
	})
}

function renderRankingTable(selector, items, labelField){
	let html = ''
	if(!items || !items.length){
		html = '<tr><td colspan="4" class="text-center text-muted">Sem dados no período</td></tr>'
	}else{
		items.forEach((item, index) => {
			const label = item[labelField] || '--'
			const qty = item.quantidade !== undefined ? item.quantidade : item.quantidade_vendas
			html += `<tr><td>${index + 1}</td><td>${label}</td><td class="text-end">${parseFloat(qty || 0).toFixed(2).replace('.', ',')}</td><td class="text-end">R$ ${convertFloatToMoeda(item.faturamento || 0)}</td></tr>`
		})
	}
	$(selector).html(html)
}

function renderPdvAuditTable(items){
	let html = ''
	if(!items || !items.length){
		html = '<tr><td colspan="6" class="text-center text-muted">Nenhuma divergência encontrada no período</td></tr>'
	}else{
		items.forEach((item) => {
			html += `<tr>
				<td>#${item.venda_caixa_id}</td>
				<td>${item.data || '--'}</td>
				<td>${item.cliente || '--'}</td>
				<td class="text-end">R$ ${convertFloatToMoeda(item.valor_venda || 0)}</td>
				<td class="text-end">R$ ${convertFloatToMoeda(item.valor_financeiro || 0)}</td>
				<td class="text-end">R$ ${convertFloatToMoeda(item.diferenca || 0)}</td>
			</tr>`
		})
	}
	$('.pdv-audit-table-body').html(html)
}

function renderBiDailyChart(series){
	if(!series) return
	const options = {
		series: [{ name: 'Vendas', data: series.valores || [] }],
		chart: { type: 'line', height: 320, toolbar: { show: false } },
		stroke: { curve: 'smooth', width: 3 },
		xaxis: { categories: series.dias || [] },
		dataLabels: { enabled: false },
		grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
		yaxis: { labels: { formatter: function(value){ return 'R$ ' + value } } }
	}
	if (biDailyChart) {
		biDailyChart.updateOptions({ xaxis: { categories: series.dias || [] }, series: [{ name: 'Vendas', data: series.valores || [] }] }, true, true)
		return
	}
	biDailyChart = new ApexCharts(document.querySelector('#chart-bi-daily'), options)
	biDailyChart.render()
}

function renderBiPaymentChart(data){
	if(!data) return
	const options = {
		series: data.valores || [],
		chart: { type: 'donut', height: 320 },
		labels: data.labels || [],
		legend: { position: 'bottom' },
		dataLabels: { enabled: true }
	}
	if (paymentChart) {
		paymentChart.updateOptions({ labels: data.labels || [], series: data.valores || [] }, true, true)
		return
	}
	paymentChart = new ApexCharts(document.querySelector('#chart-bi-payment'), options)
	paymentChart.render()
}

function renderDreYearChart(data){
	if(!data) return
	const options = {
		series: [
			{ name: 'Receita líquida', data: data.receita_liquida || [] },
			{ name: 'Custos + despesas', data: data.custos_total || [] },
			{ name: 'Lucro líquido', data: data.lucro_liquido || [] }
		],
		chart: { type: 'bar', height: 320, toolbar: { show: false } },
		plotOptions: { bar: { columnWidth: '45%', borderRadius: 4 } },
		dataLabels: { enabled: false },
		xaxis: { categories: data.meses || [] },
		grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
		yaxis: { labels: { formatter: function(value){ return 'R$ ' + value } } }
	}
	if (dreChart) {
		dreChart.updateOptions({ xaxis: { categories: data.meses || [] }, series: options.series }, true, true)
		return
	}
		dreChart = new ApexCharts(document.querySelector('#chart-dre-year'), options)
		dreChart.render()
}
