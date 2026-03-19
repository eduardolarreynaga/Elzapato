<?php include $_SERVER['DOCUMENT_ROOT'] . '/ElZapato/src/views/layouts/header.php'; ?>

<div style="max-width: 800px; margin: 40px auto; padding: 20px; background-color: #ffffff; border: 1px solid #D6C0B3; border-radius: 8px;">
    <h3 style="color: #AB886D;">Panel de Redacción del Blog</h3>
    <hr style="border: 0.5px solid #E4E0E1;">
    
    <form action="guardar_post.php" method="POST">
        <label style="display: block; margin-bottom: 5px;">Título de la entrada:</label>
        <input type="text" name="titulo" style="width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #D6C0B3;">

        <label style="display: block; margin-bottom: 5px;">Contenido:</label>
        <textarea name="contenido" rows="10" style="width: 100%; padding: 10px; border: 1px solid #D6C0B3;"></textarea>

        <button type="submit" style="margin-top: 20px; background-color: #AB886D; color: white; border: none; padding: 10px 25px; cursor: pointer; border-radius: 4px;">
            Publicar en Blog
        </button>
    </form>
</div>