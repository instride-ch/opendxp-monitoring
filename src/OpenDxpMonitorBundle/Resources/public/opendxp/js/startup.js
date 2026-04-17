class OpenDxpMonitor {
    init() {
        const user = opendxp.globalmanager.get('user');

        if (user.admin) {
            const systemHealthStatus = new Ext.Action({
                id: 'opendxp_monitor',
                text: t('opendxp_monitor_system_health_status'),
                iconCls: 'opendxp_monitor_nav_icon_health_status',
                handler: this.openSystemHealthStatusPage.bind(this),
            });

            if (layoutToolbar.extrasMenu) {
                layoutToolbar.extrasMenu.add(systemHealthStatus);
            }
        }
    }

    openSystemHealthStatusPage() {
        const systemHealthStatusPanelId = 'opendxp_monitor_system_health_status';

        try {
            opendxp.globalmanager.get(systemHealthStatusPanelId).activate();
        } catch (e) {
            opendxp.globalmanager.add(
                systemHealthStatusPanelId,
                new opendxp.tool.genericiframewindow(
                    systemHealthStatusPanelId,
                    Routing.generate('opendxp_monitor_system_health_status'),
                    'opendxp_monitor_nav_icon_health_status',
                    t('opendxp_monitor_system_health_status')
                )
            );
        }
    }
}

const opendxpMonitorHandler = new OpenDxpMonitor();

document.addEventListener(opendxp.events.opendxpReady, opendxpMonitorHandler.init.bind(opendxpMonitorHandler));
