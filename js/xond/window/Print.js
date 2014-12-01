Ext.define('Xond.window.Print', {
    extend: 'Ext.window.Window',
    xtype: 'window_print',
    title: 'Cetak',
    height: 600,
    width: 1000,
    resizable: false,
    modal: true,
    
    autoScroll: false,
    frame: true,
    border: false,
    closable: true,
    fit: true,
    layout: 'fit',
    buttonAlign:'center',

    initComponent: function() {

        var me = this;
        
        var iFrameId = "printerFrame" + Math.floor((Math.random() * 100) + 1);;

        me.items = {
            xtype : "component",
            id: iFrameId,
            autoEl : {
                id: "printFrame",
                tag : "iframe",
                src : me.src
            }
        };

        me.buttons = [{
            text: 'Cetak',
            glyph: '61487@FontAwesome',
            tooltip: 'Cetak langsung menggunakan default printer',
            handler: function() {
                
                var printFrame = Ext.get(iFrameId);
                cw = printFrame.dom.contentWindow;
                cw.print();

            }
        // },{
        //     text: 'Download PDF',
        //     glyph: '57464@font-fileformats-icons',
        //     tooltip: 'Cetak menggunakan PDF',
        //     handler: function() {

        //     }
        },{
            text: 'Batal',
            glyph: '61714@FontAwesome',
            tooltip: 'Batal mencetak',
            handler: function() {
                me.close();
            }
        }];

        this.callParent();

    }

});
