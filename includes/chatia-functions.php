<?php
// Función para generar el HTML del chatbot
function generate_chatia_html()
{
    ob_start(); // Iniciar el almacenamiento en búfer de salida
    $image_id = get_option('chatia_image_id');
?>

    <section>
        <div id="chatContainer" class="bg-white p-2 rounded-lg" style="margin: auto;">
            <!-- Heading -->
            <div class="flex flex-row space-y-1.5 pb-2 items-center border-b-gray-400 border-b-2">
                <?php
                $image_id = get_option('chatia_image_id'); // Obtener el ID de la imagen de la opción

                if ($image_id) {
                    $image_url = wp_get_attachment_image_url($image_id, 'full'); // Obtener la URL de la imagen con el tamaño completo ('full')

                    if ($image_url) {
                        echo  '<div class="relative rounded-full bg-gray-100 border w-20 h-20"><div class="h-5 w-5 bg-green-400 absolute top-0 right-0 rounded-full animate-pulse"></div><div class=" overflow-hidden rounded-full bg-gray-100 border w-20 h-20"><img loading="lazy" class="rounded-full w-20 h-20 self-center object-cover bg-[#1e1919]" src="' . esc_url($image_url) . '" alt="Imagen"></div></div>';
                    }
                }
                ?>

                <div class="pl-4">
                    <h2 class="font-semibold text-xl tracking-tight"><?php echo esc_html(get_option('chatia_chatbot_name')); ?></h2>
                    <p class="text-sm text-[#6b7280] mt-1 leading-3"><?php echo esc_html(get_option('chatia_description')); ?></p>
                </div>
                <div class="pl-1 ml-auto">

                    <?php
                    $image_logo_id = get_option('chatia_logo_id'); // Obtener el ID de la imagen de la opción

                    if ($image_logo_id) {
                        $image_logo_url = wp_get_attachment_image_url($image_logo_id, 'full'); // Obtener la URL de la imagen con el tamaño completo ('full')

                        if ($image_logo_url) {
                            echo  '<div class="relative max-w-32 h-auto"><div class=""><img loading="lazy" class="" src="' . esc_url($image_logo_url) . '" alt="Imagen"></div></div>';
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- Chat Container -->
            <div id="messageContainer" class="pr-4 h-[474px]" style="min-width: 100%; display: table;">
                <!-- Messages will appear here -->
            </div>

            <!-- Input box -->
            <div class="flex items-center pt-0">
                <input id="user-input" class=" whitespace-pre-wrap  flex h-10 w-full rounded-md border border-[#e5e7eb] px-3 py-2 text-sm placeholder-[#6b7280] focus:outline-none focus:ring-2 focus:ring-[#9ca3af] disabled:cursor-not-allowed disabled:opacity-50 text-[#030712] focus-visible:ring-offset-2" placeholder="Escribe tu mensaje" value="" onkeypress="handleKeyPress(event)">
                <button onclick="sendMessage()" class="inline-flex items-center justify-center rounded-md text-sm font-medium text-[#f9fafb] disabled:pointer-events-none disabled:opacity-50 bg-[<?php echo esc_html(get_option('chatia_button_color')); ?>] hover:bg-[#111827E6] h-10 px-4 py-2 border-x-4 border border-[#ffffff00]">Enviar</button>
            </div>
        </div>
    </section>
    <script>
        var processingMessage = false;

        function sendMessage() {
            if (processingMessage) {
                return; // Si ya se está procesando un mensaje, no hagas nada
            }

            var userMessage = document.getElementById('user-input').value.trim(); // Eliminar espacios en blanco al inicio y al final
            var sendButton = document.querySelector('button');

            // Verificar si el mensaje del usuario está vacío
            if (userMessage === "") {
                return; // Si el mensaje está vacío, no hagas nada
            }

            processingMessage = true;

            // Desactivar el botón de enviar
            sendButton.disabled = true;

            // Agregar mensaje del usuario al chat
            var chatContainer = document.getElementById('messageContainer');
            var userMessageHtml = '<div class="flex gap-3 my-4 text-gray-600 text-sm flex-1"><span class="relative flex shrink-0 overflow-hidden rounded-full w-8 h-8"><div class="rounded-full bg-gray-100 border p-1"><svg stroke="none" fill="black" stroke-width="0" viewBox="0 0 16 16" height="20" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10Z"></path></svg></div></span><p class="leading-relaxed"><span class="block font-bold text-gray-700">Tu </span>' + userMessage + '</p></div>';
            chatContainer.innerHTML += userMessageHtml;

            // Mostrar indicador de carga
            var loadingHtml = '<div class="flex gap-3 my-4 text-gray-600 text-sm flex-1 animate-pulse"><span class="relative flex shrink-0 overflow-hidden rounded-full w-8 h-8"><div class="rounded-full bg-gray-100 border"><img loading="lazy" class="rounded-full  self-center object-cover"src="<?php echo esc_url($image_url) ?>"></div></span></div>';
            chatContainer.innerHTML += loadingHtml;

            // Realiza la solicitud POST a la ruta /ask
            fetch('<?php echo esc_attr(get_option('chatia_url')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'user_message=' + encodeURIComponent(userMessage),
                })
                .then(response => response.json())
                .then(data => {
                    // Eliminar indicador de carga
                    chatContainer.removeChild(chatContainer.lastChild);

                    // Maneja la respuesta del servidor
                    var botReplyHtml = '<div class="flex gap-3 my-4 text-gray-600 text-sm flex-1"><span class="relative flex shrink-0 overflow-hidden rounded-full w-8 h-8"><div class="rounded-full bg-gray-100 border"><img loading="lazy" class="rounded-full  self-center object-cover"src="<?php echo esc_url($image_url) ?>"></div></span><div><span class="block font-bold text-gray-700"><?php echo esc_html(get_option('chatia_chatbot_name')); ?> </span><p class="leading-relaxed">' + data.bot_reply + '</p></div></div>';
                    chatContainer.innerHTML += botReplyHtml;

                    // Habilitar el botón de enviar después de recibir la respuesta
                    sendButton.disabled = false;
                    processingMessage = false;
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Eliminar indicador de carga en caso de error
                    chatContainer.removeChild(chatContainer.lastChild);
                    // Habilitar el botón de enviar en caso de error
                    sendButton.disabled = false;
                    processingMessage = false;
                });

            // Limpiar el campo de entrada del usuario
            document.getElementById('user-input').value = '';
        }

        function handleKeyPress(event) {
            if (event.keyCode === 13) { // 13 es el código de la tecla Enter
                sendMessage();
            }
        }
    </script>
<?php

    $output = ob_get_clean(); // Obtener el contenido del búfer y limpiar el búfer de salida
    return $output;
}

// Registrar el shortcode para mostrar el chatbot
function register_chatia_shortcode()
{
    add_shortcode('chatia', 'generate_chatia_html');
}

// En tu archivo de funciones o donde estés configurando tus opciones
function chatia_enqueue_media()
{
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'chatia_enqueue_media');

// Contenido de la página de opciones
function chatia_options_page_content()
{
?>
    <div class="wrap">
        <h2>Chatia Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields('chatia_options_group'); ?>
            <table class="form-table">
                <?php
                // Función para mostrar el campo de entrada de imagen
                function chatia_render_image_input($input_name, $image_id_name, $image_preview_id)
                {
                    $image_id = get_option($image_id_name);
                    $image_url = wp_get_attachment_url($image_id);
                ?>
                    <tr valign="top">
                        <th scope="row">Image</th>
                        <td>
                            <input type="hidden" name="<?php echo esc_attr($input_name); ?>" id="<?php echo esc_attr($image_id_name); ?>" value="<?php echo esc_attr($image_id); ?>">
                            <img id="<?php echo esc_attr($image_preview_id); ?>" src="<?php echo esc_attr($image_url); ?>" style="max-width: 100px; max-height: 100px;"><br>
                            <button type="button" class="button chatia-upload-image-button">Select Image</button>
                        </td>
                    </tr>
                <?php
                }
                ?>
                <?php chatia_render_image_input('chatia_image_id', 'chatia_image_id', 'chatia_image_preview'); ?>
                <tr valign="top">
                    <th scope="row">Button Color</th>
                    <td><input type="text" id="chatia_button_color" name="chatia_button_color" value="<?php echo esc_attr(get_option('chatia_button_color', '#ffffff')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Chatbot Name</th>
                    <td><input type="text" name="chatia_chatbot_name" value="<?php echo esc_attr(get_option('chatia_chatbot_name')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Description</th>
                    <td><input type="text" name="chatia_description" value="<?php echo esc_attr(get_option('chatia_description')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">URL</th>
                    <td><input type="text" name="chatia_url" value="<?php echo esc_attr(get_option('chatia_url')); ?>" /></td>
                </tr>
                <?php chatia_render_image_input('chatia_logo_id', 'chatia_logo_id', 'chatia_image_preview_logo'); ?>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var chatiaUploadImageButtons = document.querySelectorAll('.chatia-upload-image-button');

            chatiaUploadImageButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var mediaUploader = wp.media({
                        title: 'Choose Image',
                        button: {
                            text: 'Choose Image'
                        },
                        multiple: false
                    });

                    mediaUploader.on('select', function() {
                        var attachment = mediaUploader.state().get('selection').first().toJSON();
                        var imageIdInput = button.parentElement.querySelector('input[type="hidden"]');
                        var imagePreview = button.parentElement.querySelector('img');
                        imageIdInput.value = attachment.id;
                        imagePreview.src = attachment.url;
                    });

                    mediaUploader.open();
                });
            });
        });
    </script>
<?php
}




// Función para agregar opciones personalizadas al panel de administración
function chatia_register_settings()
{
    add_option('chatia_image_id', ''); // Cambiado a almacenar la ID de la imagen en lugar de la URL
    add_option('chatia_button_color', '#ffffff');
    add_option('chatia_chatbot_name', 'Chatbot');
    add_option('chatia_description', 'Powered by spawndev.uk');
    add_option('chatia_url', 'http://127.0.0.1:5000/ask');
    add_option('chatia_logo_id', ''); // Cambiado a almacenar la ID de la imagen en lugar de la URL


    register_setting('chatia_options_group', 'chatia_image_id');
    register_setting('chatia_options_group', 'chatia_button_color');
    register_setting('chatia_options_group', 'chatia_chatbot_name');
    register_setting('chatia_options_group', 'chatia_description');
    register_setting('chatia_options_group', 'chatia_url');
    register_setting('chatia_options_group', 'chatia_logo_id');
}

// Función para agregar la página de opciones al panel de administración
function chatia_options_page()
{
    add_options_page('Chatia Settings', 'Chatia Settings', 'manage_options', 'chatia', 'chatia_options_page_content');
}

// Función para agregar scripts al encabezado
function chatia_add_scripts()
{
    wp_enqueue_script('tailwind', 'https://cdn.tailwindcss.com', array(), null, false);
}

// Agregar ganchos de acción
add_action('init', 'register_chatia_shortcode');
add_action('admin_init', 'chatia_register_settings');
add_action('admin_menu', 'chatia_options_page');
add_action('wp_enqueue_scripts', 'chatia_add_scripts');
