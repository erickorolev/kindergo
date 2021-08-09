{$mainconfig}

<button type="button" class="btn btn-default pull-right" onclick="jQuery('#loadpostmeta').val('1');submitConfigForm();">load Meta tags for post type</button>
<input type="hidden" name="loadpostmeta" id="loadpostmeta" value="0" />
<h5><strong>Post Meta</strong></h5>
<hr/>
<div></div>
{$mainvaluelist}

<script type="text/template">
    <div style="display:flex;flex-direction:row;">
        <i class="fa fa-minus-square" aria-hidden="true"></i>
        <input type="text" name="metakey[##INDEX##]" class="metakey" value="" />
        <div class="metavalue"></div>
    </div>
</script>