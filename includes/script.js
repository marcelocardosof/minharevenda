jQuery(document).ready(function($) {
    
    // Para o primeiro formulário
    $('#formScreen form').submit(function(e) {
        e.preventDefault();

        // Desaparece formulário
        $('#formScreen').addClass('hide');

        // Mostrar a etapa 2
        $('#etapa1').removeClass('active');
        $('#etapa2').addClass('active');
        $('#loadingScreen').removeClass('hide');

        // Inicie a simulação da barra de progresso
        let progress = 0;
        let progressInterval = setInterval(function() {
            progress += 2;
            if (progress > 95) {
                clearInterval(progressInterval);
            }
            updateProgressBar(progress);
        }, 100);

        // Pega o valor do input
        let novaPasta = $('#novaPasta').val();
let modeloSelecionado = $('input[name="modelo"]:checked').val();



        // Envie o formulário via AJAX
$.post(mpc_vars.cloneModelPath, { novaPasta: novaPasta, modelo: modeloSelecionado }, function(data) {
            clearInterval(progressInterval); // Pare o intervalo
            updateProgressBar(100); // Defina a barra de progresso para 100%
            
            // Mostrar a etapa 3
            setTimeout(function() {
                $('#loadingScreen').addClass('hide');
                $('#etapa2').removeClass('active');
                $('#etapa3').addClass('active');
                $('#successScreen').removeClass('hide');
            }, 500); // Adicione uma pequena pausa antes de mostrar a etapa 3
            
            // Atualizar os links na tela de sucesso
            $('#successScreen a').each(function() {
                let href = $(this).attr('href');
                $(this).attr('href', href.replace("PASTA_NOVA", novaPasta));
            });
            $('#pastaVisivel').text(novaPasta);
            $('#pastaVisivel2').text(novaPasta);
        });
    });

    // Para o segundo formulário
   $('#uploadForm').submit(function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        $.ajax({
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percent = Math.round((e.loaded / e.total) * 100);
                        $('#uploadProgressBar').css('width', percent + '%').text(percent + '%');
                    }
                });
                return xhr;
            },
            type: 'POST',
            url: mpc_vars.uploadModelPath, // caminho direto para o arquivo PHP
            data: formData, // removemos $.extend e a chave 'action'
            processData: false,
            contentType: false,
            success: function(data) {
                alert(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Erro AJAX: " + textStatus, errorThrown);
            }
        });
    });
    
    function updateProgressBar(percentage) {
        $('.progress-bar').css('width', percentage + '%')
            .attr('aria-valuenow', percentage)
            .text(percentage + '%');
    }
});