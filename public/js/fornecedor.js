(function () {
    function initFornecedorModal() {
        $('#modal-fornecedor .select2').each(function () {
            let id = $(this).prop('id');

            if (id === 'inp-uf') {
                $(this).select2({
                    dropdownParent: $('#modal-fornecedor'),
                    theme: 'bootstrap4',
                    width: '100%'
                });
            }

            if (id === 'inp-cidade_id') {
                $(this).select2({
                    minimumInputLength: 2,
                    language: "pt-BR",
                    placeholder: "Digite para buscar a cidade",
                    width: "100%",
                    theme: 'bootstrap4',
                    dropdownParent: $('#modal-fornecedor'),
                    ajax: {
                        cache: true,
                        url: path_url + 'api/buscaCidades',
                        dataType: "json",
                        data: function (params) {
                            return { pesquisa: params.term };
                        },
                        processResults: function (response) {
                            var results = [];
                            $.each(response, function (i, v) {
                                results.push({
                                    id: v.id,
                                    text: v.nome + "(" + v.uf + ")",
                                    value: v.id
                                });
                            });
                            return { results: results };
                        }
                    }
                });
            }
        });
    }

    function findCidadeFornecedor(codigo_ibge) {
        $.get(path_url + "api/cidadePorCodigoIbge/" + codigo_ibge)
            .done((res) => {
                var newOption = new Option(
                    res.nome + " (" + res.uf + ")",
                    res.id,
                    false,
                    false
                );
                $("#inp-cidade_id").html(newOption).trigger("change");
            })
            .fail((err) => console.log(err));
    }

    $(document).on('shown.bs.modal', '#modal-fornecedor', function () {
        initFornecedorModal();
    });

    function collectFornecedorModalData() {
        let data = {};

        $('#modal-fornecedor').find('input, select, textarea').each(function () {
            let indice = $(this).attr('id');
            if (!indice || !indice.startsWith('inp-')) return;

            indice = indice.substring(4);
            data[indice] = $(this).val();
        });

        data['empresa_id'] = $('#empresa_id').val();

        return data;
    }

    function resetFornecedorModalForm() {
        const $modal = $('#modal-fornecedor');
        const $form = $('#form-fornecedor-modal');

        if ($form.length > 0) {
            $form[0].reset();
        }

        $modal.find('#inp-cidade_id').html('').trigger('change');
    }

    $(document).on('submit', '#form-fornecedor-modal', function (e) {
        e.preventDefault();

        const $button = $('#btn-store-fornecedor');
        if ($button.prop('disabled')) {
            return;
        }

        let valid = typeof validaCamposModal === 'function' ? validaCamposModal('#modal-fornecedor') : [];
        if (valid.length > 0) {
            let msg = "";
            valid.map((x) => { msg += x + "\n"; });
            swal("Ops, erro no formulário", msg, "error");
            return;
        }

        const originalText = $button.html();
        $button.prop('disabled', true).html('Salvando...');

        $.post(path_url + 'api/fornecedor/store', collectFornecedorModalData())
            .done((success) => {
                swal("Sucesso", "Fornecedor cadastrado!", "success")
                    .then(() => {
                        var label = success.razao_social + ' - ' + success.cpf_cnpj;
                        var $fornecedorSelect = $('#inp-fornecedor_id');

                        if ($fornecedorSelect.length) {
                            var existingOption = $fornecedorSelect.find("option[value='" + success.id + "']");

                            if (existingOption.length) {
                                existingOption.text(label);
                            } else {
                                var newOption = new Option(label, success.id, false, false);
                                $fornecedorSelect.append(newOption);
                            }

                            $fornecedorSelect.val(String(success.id)).trigger('change');
                            $fornecedorSelect.trigger({
                                type: 'select2:select',
                                params: {
                                    data: {
                                        id: success.id,
                                        text: label,
                                        value: success.id
                                    }
                                }
                            });
                        }

                        resetFornecedorModalForm();
                        $('#modal-fornecedor').modal('hide');
                    });
            })
            .fail((err) => {
                console.log(err);
                let msg = "Algo deu errado ao salvar fornecedor!";
                if (err.responseJSON) {
                    if (typeof err.responseJSON === 'string') msg = err.responseJSON;
                    else if (err.responseJSON.message) msg = err.responseJSON.message;
                }
                swal("Ops", msg, "error");
            })
            .always(() => {
                $button.prop('disabled', false).html(originalText);
            });
    });

    $(document).on('click', '#btn-consulta-cnpj', function (e) {
        e.preventDefault();

        let cnpj = ($("#inp-cpf_cnpj").val() || '').replace(/[^0-9]/g, '');
        if (cnpj.length !== 14) {
            swal("Alerta", "Informe o CNPJ corretamente", "warning");
            return;
        }

        $.get('https://publica.cnpj.ws/cnpj/' + cnpj)
            .done((data) => {
                let ie = '';
                if (data.estabelecimento && data.estabelecimento.inscricoes_estaduais && data.estabelecimento.inscricoes_estaduais.length > 0) {
                    ie = data.estabelecimento.inscricoes_estaduais[0].inscricao_estadual || '';
                }

                $('#inp-ie_rg').val(ie);
                $("#inp-razao_social").val(data.razao_social || '');
                $("#inp-nome_fantasia").val((data.estabelecimento && data.estabelecimento.nome_fantasia) || '');
                $("#inp-rua").val(((data.estabelecimento && data.estabelecimento.tipo_logradouro) || '') + " " + ((data.estabelecimento && data.estabelecimento.logradouro) || ''));
                $("#inp-numero").val((data.estabelecimento && data.estabelecimento.numero) || '');
                $("#inp-bairro").val((data.estabelecimento && data.estabelecimento.bairro) || '');

                let cep = ((data.estabelecimento && data.estabelecimento.cep) || '').replace(/[^\d]+/g, '');
                if (cep.length >= 8) {
                    $('#inp-cep').val(cep.substring(0, 5) + '-' + cep.substring(5, 8));
                }

                if (data.estabelecimento && data.estabelecimento.cidade && data.estabelecimento.cidade.ibge_id) {
                    findCidadeFornecedor(data.estabelecimento.cidade.ibge_id);
                }
            })
            .fail((err) => {
                console.log(err);
                let titulo = err.responseJSON && err.responseJSON.titulo ? err.responseJSON.titulo : 'Falha ao consultar CNPJ';
                swal("Alerta", titulo, "warning");
            });
    });

    $(document).on("blur", "#inp-cep", function () {
        let cep = ($(this).val() || '').replace(/[^0-9]/g,'');
        $.get("https://viacep.com.br/ws/" + cep + "/json")
            .done((success) => {
                $('#inp-rua').val(success.logradouro || '');
                $('#inp-bairro').val(success.bairro || '');
                if (success.ibge) {
                    findCidadeFornecedor(success.ibge);
                }
            })
            .fail((err) => console.log(err));
    });
})();
