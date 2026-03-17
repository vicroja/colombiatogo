<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<div class="content-wrapper" style="background-color: #d1d7db; height: calc(100vh - 50px); overflow: hidden;">
    <div class="container-fluid h-100 p-0">
        <div class="row no-gutters h-100">

            <div id="chatSidebar" class="col-md-4 col-lg-3 d-none d-md-flex flex-column border-right bg-white h-100 shadow-lg">
                <button type="button" id="closeSidebarBtn" class="btn btn-sm btn-light position-absolute d-md-none" style="top: 10px; right: 10px; z-index: 10;">
                    <i class="fas fa-times"></i>
                </button>

                <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-secondary font-weight-bold">
                        <i class="fas fa-comments mr-2"></i>Chats
                    </h5>
                    <a href="<?= site_url('chatpanel/conversations') ?>" class="btn btn-sm btn-link text-muted" title="Ver Lista Completa">
                        <i class="fas fa-list"></i>
                    </a>
                </div>

                <div class="p-2 bg-white border-bottom">
                    <div class="input-group">
                        <input type="text" id="contact-search" class="form-control form-control-sm border-right-0" placeholder="Buscar huésped...">
                        <div class="input-group-append">
                            <span class="input-group-text bg-white border-left-0"><i class="fas fa-search text-muted"></i></span>
                        </div>
                    </div>
                </div>

                <div id="contacts-list" class="flex-grow-1 overflow-auto">
                    <div class="text-center p-4 text-muted">
                        <i class="fas fa-spinner fa-spin mb-2"></i><br>Cargando chats...
                    </div>
                </div>
            </div>

            <div class="col-md-8 col-lg-9 d-flex flex-column h-100 position-relative">

                <div class="p-3 bg-white border-bottom shadow-sm d-flex justify-content-between align-items-center z-index-1">
                    <div class="d-flex align-items-center">
                        <button type="button" id="toggleSidebarBtn" class="btn btn-light d-md-none mr-2">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div>
                            <h5 class="mb-0 font-weight-bold" id="chat-header-name">Selecciona un chat</h5>
                            <small class="text-success" id="chat-header-status"><i class="fas fa-robot"></i> IA Activa</small>
                        </div>
                    </div>
                    <div>
                        <button id="returnToAiBtn" class="btn btn-sm btn-success" disabled>
                            <i class="fas fa-robot"></i> Devolver a la IA
                        </button>
                    </div>
                </div>

                <div id="chat-messages" class="flex-grow-1 p-3 overflow-auto" style="background-image: url('<?= base_url('assets/img/whatsapp-bg.png') ?>');">
                    <div class="d-flex h-100 justify-content-center align-items-center text-muted text-center">
                        <div>
                            <i class="fab fa-whatsapp fa-4x mb-3 text-success opacity-50"></i>
                            <h4>Asistente PMS Multi-Tenant</h4>
                            <p>Selecciona un chat de la barra lateral para comenzar</p>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-light border-top">
                    <form id="manualMessageForm" class="d-flex align-items-center">
                        <input type="hidden" id="chat_contact_phone_for_modal" name="phone" value="">

                        <div class="input-group">
                            <input type="text" id="manual_message_input" name="message" class="form-control rounded-pill mr-2" placeholder="Escribe un mensaje manual..." disabled required>
                            <div class="input-group-append">
                                <button type="submit" id="sendManualBtn" class="btn btn-primary rounded-circle shadow-sm" style="width: 40px; height: 40px; padding: 0;" disabled>
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<script type="text/javascript">
    var base_url = "<?= base_url() ?>/";
</script>

<script src="<?= base_url('assets/js/whatsapp/whatsapp_chat_improved.js') ?>?v=<?= filemtime(FCPATH . 'assets/js/whatsapp/whatsapp_chat_improved.js') ?>"></script>

<style>
    /* Ajustes visuales para el backdrop en móviles */
    .sidebar-backdrop { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); z-index: 1030; display: none; }
    .sidebar-backdrop.show { display: block; }
    #chatSidebar { z-index: 1040; transition: transform 0.3s ease; }
    @media (max-width: 767.98px) {
        #chatSidebar { position: fixed; left: -100%; width: 280px; }
        #chatSidebar.active { left: 0; }
    }
</style>