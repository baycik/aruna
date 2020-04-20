<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-module" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
            <h1><?php echo $heading_title; ?></h1>
            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
        <?php if(!empty($iskeysinstalled)){ ?>
            <div class="alert alert-success alert-dismissible"><i class="fa fa-exclamation-circle"></i> <?php echo $iskeysinstalled; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php } ?>
        <?php if ($error_warning) { ?>
            <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
              <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php } ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-module" class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
                        <div class="col-sm-10">
                            <select name="status" id="input-status" class="form-control">
                                <?php if($module_iss_filter_status){ ?> 
                                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                    <option value="0"><?php echo $text_disabled; ?></option>
                                <?php } else {  ?> 
                                    <option value="1"><?php echo $text_enabled ; ?></option>
                                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                <?php }  ?> 
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-status"><?php echo $install_keys ; ?></label>
                        <div class="col-sm-10">
                            <a href="<?php echo $installKeys; ?>"><button type="button" class="btn btn-success"><i class="fa fa-plus"></i> <?php echo $install_keys ; ?></button></a>
                            <a href="<?php echo $uninstallKeys; ?>"><button type="button" class="btn btn-danger"><i class="fa fa-minus"></i> <?php echo $uninstall_keys ; ?></button></a>
                        </div>
                    </div>
                    <?php if(!empty($module_id)){ ?>
                        <input type="hidden" name="module_id" value="<?php echo $module_id ; ?>" class="form-control"/>
                    <?php } ?>
                </form>
            </div>
        </div>
    </div>
</div>
<?php echo $footer ; ?>
