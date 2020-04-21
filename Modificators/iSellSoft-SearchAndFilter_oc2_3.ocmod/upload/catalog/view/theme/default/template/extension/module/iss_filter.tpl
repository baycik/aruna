<form id="filter_form">
<div class="panel panel-default">
    <div class="panel-heading"><?php echo $heading_title;  ?></div>

    <div class="list-group"> 
        <a class="list-group-item a" id="list-group-item_pricefilter" data-toggle="collapse" href="#collapse_pricefilter">
            <?php echo $price_title;  ?>
            <i class="fa fa-check-square" style="color:#5aa4ed; display:none"></i>
            <span style="float:right; transform: rotate(0deg);margin: 5%;" class="caret"></span>
        </a>
        <div class="list-group-item">
            <div class="panel-collapse collapse in" id="collapse_pricefilter" style="padding:10px">
                <div style="margin-bottom: 10px;text-align: left">
                    <input type="number" id="filter_price_min" name="min" value="<?php echo round($filter_min);  ?>" style="width:40%;margin: 0px" />
                    <input type="number" id="filter_price_max" name="max" value="<?php echo round($filter_max);  ?>" style="width:40%;float: right" />
                </div>
                <div id="filter_price_slider" style="clear: both"></div>
            </div>
        </div>
    <?php foreach( $filter['filter_groups'] as $filter_group ){ ?> 
    <a class="list-group-item a" id="list-group-item_<?php echo $filter_group['filter_group_id'];  ?>" data-toggle="collapse" href="#collapse_<?php echo $filter_group['filter_group_id'];  ?>">
            <?php echo $filter_group['name'];  ?>
            <i class="fa fa-check-square" style="color:#5aa4ed; display:none"></i>
            <span style="float:right; transform: rotate(90deg);margin: 5%;" class="caret"></span>
        </a>
        <div class="list-group-item"  style="padding:0 15px">
            <div id="collapse_<?php echo $filter_group['filter_group_id'];  ?>" class="panel-collapse collapse">
                <?php foreach( $filter_group['filter'] as $filter ){ ?>
                    <div class="checkbox">
                        <label>
                            <?php if (in_array( $filter['filter_id'], $filter_category )){ ?>
                                <input type="checkbox" name="filter[]" value="<?php echo $filter['filter_id'];  ?>" checked="checked" />
                            <?php echo $filter['name']; 
                                } else { ?>
                                <input type="checkbox" name="filter[]" value="<?php echo $filter['filter_id'];  ?>" />
                                <?php if (count($filter) < 1){ ?>
                                    <span style="color:#999"><i><?php echo $filter['name'];  ?></i></span>
                                    <?php } else {
                                        echo $filter['name'];  
                                    } ?>
                            <?php } ?>
                        </label>
                    </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    </div>
    <div class="panel-footer text-right">
        <button id="button-filter" class="btn btn-primary" style="width:100%"><?php echo $button_filter;  ?></button>
    </div>
</div>
    </form>
<script type="text/javascript">
    var available_min=Math.round(<?php echo $min_price_available;  ?>);
    var available_max=Math.round(<?php echo $max_price_available;  ?>);
    var selected_min=Math.round(<?php echo $filter_min;  ?>);
    var selected_max=Math.round(<?php echo $filter_max;  ?>);
    var global_filter_clock;
    function do_filter(){
        var filter = [];
        $('#filter_form input[name^=\'filter\']:checked').each(function (element) {
            filter.push(this.value);
        });
        var url='<?php echo $action;  ?>&filter=' + filter.join(',');
        var min_input_value=Math.round($('#filter_form input[name=min]').val() );
        var max_input_value=Math.round($('#filter_form input[name=max]').val() );
        url+='&min='+min_input_value;
        url+='&max='+max_input_value;
        location.href = url;
    }
    $("#filter_form").submit(function(event){
        event.preventDefault();
        do_filter();
        
    });
    $('#filter_form input').on('change', function () {
        clearTimeout(global_filter_clock);
        global_filter_clock = setTimeout(do_filter, 1000);
    });
    $('.list-group-item.a').on('click', function (event) {
        var header_node=$(event.target);
        if( !$(event.target).hasClass('list-group-item') ){
            header_node=$(event.target).parent();
        }
        var panel_node = header_node.next();
        if ( panel_node.find('.panel-collapse').hasClass('in') ) {
            $(header_node).find(".caret").css("transform", "rotate(90deg)");
        } else{
            $(header_node).find(".caret").css("transform", "rotate(0deg)");
        }
    });
    $(document).ready(function () {
        var boxes = $('input[name^=\'filter\']:checked');
        if (boxes.length > 0) {
            for (var i = 0; i < boxes.length; i++) {
                $(boxes[i].parentElement.parentElement.parentElement).removeClass('collapse');
                $(boxes[i].parentElement.parentElement.parentElement).addClass('collapse in');
                $(boxes[i].parentElement.parentElement.parentElement.parentElement.previousElementSibling.firstElementChild).css('display', "inline-block");
                $(boxes[i].parentElement.parentElement.parentElement.parentElement.previousElementSibling.lastElementChild).css("transform", "rotate(0deg)");
            }
        };
        
        
        var min_input = document.getElementById('filter_price_min');
        var max_input = document.getElementById('filter_price_max');
        var slider = document.getElementById('filter_price_slider');
        noUiSlider.create(slider, {
            start: [selected_min, selected_max],
            connect: true,
            range: {
                'min': available_min,
                'max': available_max+1
            }
        });
        slider.noUiSlider.on('update', function (values, handle) {
            var min_value = Math.round(values[0]);
            var max_value = Math.round(values[1]);
            var min_input_value=Math.round(min_input.value);
            var max_input_value=Math.round(max_input.value);
            if( (min_input_value-min_value)!=0 || (max_input_value-max_value)!=0 ){
                min_input.value = min_value;
                max_input.value = max_value;
                clearTimeout(global_filter_clock);
                global_filter_clock = setTimeout(do_filter, 1000);
            }
        });
        min_input.addEventListener('change', function () {
            slider.noUiSlider.set([this.value, null]);
        });
        max_input.addEventListener('change', function () {
            slider.noUiSlider.set([null, this.value]);
        });
    });
</script>

<!-- must be included via modificator -->
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/noUiSlider/13.1.0/nouislider.min.js"></script>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/noUiSlider/13.1.0/nouislider.min.css">
<style>
.noUi-connect {

    background: #ccc;

}
    </style>