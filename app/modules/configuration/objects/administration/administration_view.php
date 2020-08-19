<?php
/**
 * Created by PhpStorm.
 * User: NelsonRodriguez
 * Date: 23/04/2019
 * Time: 11:14 AM
 */

class administration_view extends global_config implements window_view
{
    private static $_instance;
    private $strAction = "";

    public function __construct($arrParams)
    {
        parent::__construct($arrParams);
    }

    public function setStrAction($strAction)
    {
        $this->strAction = $strAction;
    }

    public static function getInstance($arrParams)
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($arrParams);
        }
        return self::$_instance;
    }

    public function draw()
    {
        draw_header();
        theme_draw_centerbox_open($this->lang["NOTIFICATION_ADMIN"]);
        jquery_includeLibrary("chosen");
        ?>
            <link rel="stylesheet" href="modules/configuration/objects/administration/css/styles.css" type="text/css" />
            <div id="cntAllWindowAdminNotifications" class="col-xs-12"></div>
        <?php

        $this->scripts();
        theme_draw_centerbox_close();
        draw_footer();
    }

    public function scripts()
    {
        ?>
            <script src="modules/configuration/objects/administration/js/adminNotifications.js"></script>
            <script type="application/ecmascript">

                const strAction = `<?php print $this->strAction; ?>`;
                const dw = new drawWidgets();
                const htmlDOM = document.implementation.createHTMLDocument();
                const lang = {
                    NOTIFICATION_ADD: '<?php print $this->lang["NOTIFICATION_ADD"] ?>',
                };
                const adminNotifications = new Notifications({
                    strAction: strAction,
                    htmlDOM: htmlDOM,
                });
                let objNotifications = {};
                adminNotifications.setDrawWidget(dw);

                $( () => {
                    fetch(`${strAction}&op=getSwUserTypes`)
                        .then( (response) => {
                            return response.json();
                        })
                        .then( (data) => {
                            if(data.status === 'ok'){
                                adminNotifications.setSwUserTypes(data.profiles);
                                adminNotifications.setWindowsExist(data.windows);
                            }
                        })
                        .catch( () => {
                            //catch
                        });


                    drawInitWindow();
                });

                function drawInitWindow()
                {
                    //const cntAll = $(`#cntAllWindowAdminNotifications`);
                    const cntAll = document.getElementById('cntAllWindowAdminNotifications');
                    drawWindowContainers(cntAll);
                }

                function drawWindowContainers(cntAll)
                {
                    const window = `<div id="cntButtonCreateNotification" class="col-xs-12">
                                        <button class="btn btn-primary-outline floatRightMargin" id="btnDrawFormCreateNotification">
                                            <i class="fa fa-plus"></i>
                                            ${lang.NOTIFICATION_ADD}
                                        </button>
                                    </div>
                                    <div class="col-xs-12">
                                        <h2>
                                            Notificaciones Existentes:
                                        </h2>
                                    </div>
                                    <div id="cntNotificationsExist" class="col-xs-12"></div>`;
                    htmlDOM.body.innerHTML = window;
                    cntAll.append(htmlDOM.body);

                    const btnCreateNotification = document.getElementById('btnDrawFormCreateNotification');

                    btnCreateNotification.addEventListener('click', () => {
                        adminNotifications.showFormNotification();
                    });

                    const cntNotifications = document.getElementById('cntNotificationsExist');
                    adminNotifications.getNotificationsActive(cntNotifications);
                }

                function saveNotification(intIDNotification = 0, strForm = '')
                {
                    let strAllProfileAccess = '';
                    if(Object.keys(objNotifications).length > 0){
                        for(let key in objNotifications){
                            const val = objNotifications[key];
                            strAllProfileAccess = `${val},${strAllProfileAccess}`;
                        }
                    }

                    const form = $(`#${strForm}`).serialize();
                    fetch(`${strAction}&op=saveNotification&notification=${intIDNotification}&${form}&strProfileAccess=${strAllProfileAccess}`)
                        .then( (response) => {
                            return response.json();
                        })
                        .then( (data) => {
                            if(data.status === 'ok'){
                                location.reload();
                            }
                        })
                        .catch( () => {
                            //hi this is a catch
                        })
                }

            </script>
        <?php
    }

}