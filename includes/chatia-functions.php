<?php
// Función para generar el HTML del chatbot
function generate_chatia_html()
{
    ob_start(); // Iniciar el almacenamiento en búfer de salida

?>
    <section>
        <div id="chatContainer" class="bg-white p-2 rounded-lg" style="margin: auto;">
            <!-- Heading -->
            <div class="flex flex-row space-y-1.5 pb-6 items-center">
                <img loading="lazy" class="rounded-full w-20 h-20 self-center object-cover" src="<?php echo esc_url(get_option('chatia_image_url')); ?>">
                <div>
                    <h2 class="font-semibold text-lg tracking-tight"><?php echo esc_html(get_option('chatia_chatbot_name')); ?></h2>
                    <p class="text-sm text-[#6b7280] leading-3"><?php echo esc_html(get_option('chatia_description')); ?></p>
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
            var loadingHtml = '<div class="flex gap-3 my-4 text-gray-600 text-sm flex-1 animate-pulse"><span class="relative flex shrink-0 overflow-hidden rounded-full w-8 h-8"><div class="rounded-full bg-gray-100 border"><img loading="lazy" class="rounded-full  self-center object-cover"src="https://static.vecteezy.com/system/resources/previews/000/550/731/original/user-icon-vector.jpg"></div></span></div>';
            chatContainer.innerHTML += loadingHtml;

            // Realiza la solicitud POST a la ruta /ask
            fetch('http://127.0.0.1:5000/ask', {
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
                    var botReplyHtml = '<div class="flex gap-3 my-4 text-gray-600 text-sm flex-1"><span class="relative flex shrink-0 overflow-hidden rounded-full w-8 h-8"><div class="rounded-full bg-gray-100 border"><img loading="lazy" class="rounded-full  self-center object-cover"src="https://static.vecteezy.com/system/resources/previews/000/550/731/original/user-icon-vector.jpg"></div></span><div><span class="block font-bold text-gray-700">AI </span><p class="leading-relaxed">' + data.bot_reply + '</p></div></div>';
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

// Contenido de la página de opciones
function chatia_options_page_content()
{
?>
    <div class="wrap">
        <h2>Chatia Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields('chatia_options_group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Image</th>
                    <td>
                        <?php
                        $image_id = get_option('chatia_image_id');
                        $image_url = wp_get_attachment_url($image_id);
                        ?>
                        <input type="hidden" name="chatia_image_id" id="chatia_image_id" value="<?php echo esc_attr($image_id); ?>">
                        <img id="chatia_image_preview" src="<?php echo esc_attr($image_url); ?>" style="max-width: 100px; max-height: 100px;"><br>
                        <button type="button" class="button" id="chatia_upload_image_button">Select Image</button>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Button Color</th>
                    <td>
                        <?php
                        $button_color = get_option('chatia_button_color');
                        if (empty($button_color)) {
                            $button_color = '#ffffff';
                        }
                        ?>
                        <input type="text" id="chatia_button_color" name="chatia_button_color" value="<?php echo esc_attr($button_color); ?>" />
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Chatbot Name</th>
                    <td><input type="text" name="chatia_chatbot_name" value="<?php echo esc_attr(get_option('chatia_chatbot_name')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Description</th>
                    <td><input type="text" name="chatia_description" value="<?php echo esc_attr(get_option('chatia_description')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>

    <script>
        jQuery(document).ready(function($) {
            // Selector de colores
            $('#chatia_button_color').wpColorPicker();

            // Manejo del cargador de medios para la imagen
            $('#chatia_upload_image_button').click(function(e) {
                e.preventDefault();
                var image = wp.media({
                        title: 'Select Image',
                        multiple: false
                    }).open()
                    .on('select', function(e) {
                        var uploaded_image = image.state().get('selection').first();
                        var image_url = uploaded_image.toJSON().url;
                        $('#chatia_image_id').val(uploaded_image.id);
                        $('#chatia_image_preview').attr('src', image_url);
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

    register_setting('chatia_options_group', 'chatia_image_id');
    register_setting('chatia_options_group', 'chatia_button_color');
    register_setting('chatia_options_group', 'chatia_chatbot_name');
    register_setting('chatia_options_group', 'chatia_description');
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
