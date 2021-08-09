<div class="modelContainer" style="width:800px;">
    <form method="POST" id="popupForm" action="index.php?module=Workflow2&parent=Settings&action=TaskRepoSave" enctype="multipart/form-data">
        <div class="modal-header contentsBackground">
            <button class="close" aria-hidden="true" data-dismiss="modal" type="button" title="{vtranslate('LBL_CLOSE')}">x</button>
            <h3>{vtranslate('Export Block by Text', 'Settings:Workflow2')}</h3>
        </div>
        <div style="padding: 10px;">{* Content Start *}
            <p>{vtranslate('You can copy this text to another VtigerCRM installation and will get the exact same configuration of the blocks you selected.', 'Settings:Workflow2')}</p>
            <p class="alert alert-notice"><strong>{vtranslate('Pay Attention', 'Settings:Workflow2')}:</strong> {vtranslate("If the other system would have different fieldnames, the configurations, which use fields probably will not work in the other system.","Settings:Workflow2")}</p>
            <textarea name="data" style="width:100%;height:200px;" readonly="readonly" id="BlockData">{$data}</textarea>
        </div> {* Content Ende *}
        <div class="modal-footer quickCreateActions">
            <button class="cancelLinkContainer btn btn-success" type="reset" data-dismiss="modal" ><strong>{vtranslate('LBL_CLOSE', $MODULE)}</strong></button>
        </div>
    </form>
</div>



