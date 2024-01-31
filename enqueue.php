<?php

// Função para enfileirar os scripts e estilos
function meu_plugin_enqueue_scripts() {
    // Enfileira o Bootstrap CSS
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');

    // Enfileira jQuery (o WordPress já vem com jQuery, então vamos usar a versão embutida)
    wp_enqueue_script('jquery');

    // Enfileira Popper.js
    wp_enqueue_script('popper', 'https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js', array('jquery'), null, true);

    // Enfileira o Bootstrap JS
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery', 'popper'), null, true);
}
add_action('wp_enqueue_scripts', 'meu_plugin_enqueue_scripts');
?>