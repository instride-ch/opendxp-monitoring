class OpenDxpMonitoring {
    init() {
        const user = opendxp.globalmanager.get('user');

        if (user.admin) {
            const systemHealthStatus = new Ext.Action({
                id: 'opendxp_monitoring',
                text: t('opendxp_monitoring_system_health_status'),
                iconCls: 'opendxp_monitoring_nav_icon_health_status',
                handler: this.openSystemHealthStatusPage.bind(this),
            });

            if (layoutToolbar.extrasMenu) {
                layoutToolbar.extrasMenu.add(systemHealthStatus);
            }
        }
    }

    openSystemHealthStatusPage() {
        const systemHealthStatusPanelId = 'opendxp_monitoring_system_health_status';

        try {
            opendxp.globalmanager.get(systemHealthStatusPanelId).activate();
        } catch (e) {
            opendxp.globalmanager.add(
                systemHealthStatusPanelId,
                new opendxp.tool.genericiframewindow(
                    systemHealthStatusPanelId,
                    Routing.generate('opendxp_monitoring_system_health_status'),
                    'opendxp_monitoring_nav_icon_health_status',
                    t('opendxp_monitoring_system_health_status')
                )
            );
        }
    }
}

const opendxpMonitoringHandler = new OpenDxpMonitoring();

document.addEventListener(opendxp.events.opendxpReady, opendxpMonitoringHandler.init.bind(opendxpMonitoringHandler));
