$(document).ready(function() {
    $('form').submit(function(e) {
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
            progress += 2; // Aumente o valor conforme necessário para ajustar a velocidade
            if (progress > 95) { // Pare em 95% para aguardar a confirmação do AJAX
                clearInterval(progressInterval);
            }
            updateProgressBar(progress);
        }, 100); // Ajuste o intervalo conforme necessário

//---
let novaPasta = $('#novaPasta').val();

        // Envie o formulário via AJAX
        $.post('processClone.php', $(this).serialize(), function(data) {
            clearInterval(progressInterval); // Pare o intervalo
            updateProgressBar(100); // Defina a barra de progresso para 100%
            
            // Mostrar a etapa 3
            setTimeout(function() {
                $('#loadingScreen').addClass('hide');
                $('#etapa2').removeClass('active');
                $('#etapa3').addClass('active');
                $('#successScreen').removeClass('hide');
            }, 500); // Adicione uma pequena pausa antes de mostrar a etapa 3
        });
        
        // Atualizar os links na tela de sucesso
$('#successScreen a').each(function() {
    let href = $(this).attr('href');
    $(this).attr('href', href.replace("PASTA_NOVA", novaPasta));
});
$('#pastaVisivel').text(novaPasta);
$('#pastaVisivel2').text(novaPasta);

    });
});

function updateProgressBar(percentage) {
    $('.progress-bar').css('width', percentage + '%')
        .attr('aria-valuenow', percentage)
        .text(percentage + '%');
}
