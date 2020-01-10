/*
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2020 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 *
 */

pimcore.registerNS('coreshop.notification.rule.conditions.messageType');

coreshop.notification.rule.conditions.messageType = Class.create(coreshop.rules.conditions.abstract, {
    type: 'messageType',

    getForm: function () {
        this.form = Ext.create('Ext.form.FieldSet', {
            items: [
                {
                    xtype: 'combo',
                    fieldLabel: t('coreshop_condition_messageType'),
                    name: 'messageType',
                    value: this.data ? this.data.messageType : null,
                    width: 250,
                    store: [['customer', t('coreshop_message_type_customer')], ['customer-reply', t('coreshop_message_type_customer_reply')], ['contact', t('coreshop_message_type_contact')]],
                    triggerAction: 'all',
                    typeAhead: false,
                    editable: false,
                    forceSelection: true,
                    queryMode: 'local'
                }
            ]
        });

        return this.form;
    }
});
