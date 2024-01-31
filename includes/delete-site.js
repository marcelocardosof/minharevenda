jQuery(document).ready(function($) {
    $('.btn-delete-site').click(function() {
        var siteId = $(this).data('id');
        
        // Solicita confirmação do usuário
        var confirmation = confirm("Você realmente deseja excluir? A exclusão é definitiva e elimina todo o site!");

        // Se o usuário confirmar, prossegue com a ação de exclusão
        if (confirmation) {
            $.ajax({
                url: pluginInfo.url + '/delete-site.php',
                method: 'POST',
                dataType: 'json',  // <-- Adicione esta linha
                data: {
                    'action': 'delete_site',
                    'site_id': siteId
                },
success: function(response) {
    if (response.success) {
        alert('Site excluído com sucesso.');
        location.reload(); // Recarregar a página para atualizar a lista
    } else {
        // Vamos verificar se 'message' está definido
        var errorMessage = response.message ? response.message : 'Erro ao excluir site.';
        alert(errorMessage);
    }
}


            });
        }
    });
});
