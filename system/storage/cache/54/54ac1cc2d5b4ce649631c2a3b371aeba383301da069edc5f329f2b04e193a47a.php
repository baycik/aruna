<?php

/* upgrade/upgrade.twig */
class __TwigTemplate_796eff0861213e21fd8905b54eb1f41002aec877e9aa585ed483dbb630e489eb extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo (isset($context["header"]) ? $context["header"] : null);
        echo "
<div class=\"container\">
  <header>
    <div class=\"row\">
      <div class=\"col-sm-6\">
        <h1 class=\"pull-left\">1<small>/2</small></h1>
        <h3>";
        // line 7
        echo (isset($context["heading_title"]) ? $context["heading_title"] : null);
        echo "<br>
          <small>";
        // line 8
        echo (isset($context["text_upgrade"]) ? $context["text_upgrade"] : null);
        echo "</small></h3>
      </div>
      <div class=\"col-sm-6\">
        <div id=\"logo\" class=\"pull-right hidden-xs\"><img src=\"view/image/logo.png\" alt=\"OpenCart\" title=\"OpenCart\" /></div>
      </div>
    </div>
  </header>
  <div class=\"row\">
    <div class=\"col-sm-9\">
      <h3>";
        // line 17
        echo (isset($context["text_server"]) ? $context["text_server"] : null);
        echo "</h3>
      <fieldset>
        <ol>
          <li>";
        // line 20
        echo (isset($context["text_error"]) ? $context["text_error"] : null);
        echo "</li>
          <li>";
        // line 21
        echo (isset($context["text_clear"]) ? $context["text_clear"] : null);
        echo "</li>
          <li>";
        // line 22
        echo (isset($context["text_admin"]) ? $context["text_admin"] : null);
        echo "</li>
          <li>";
        // line 23
        echo (isset($context["text_user"]) ? $context["text_user"] : null);
        echo "</li>
          <li>";
        // line 24
        echo (isset($context["text_setting"]) ? $context["text_setting"] : null);
        echo "</li>
          <li>";
        // line 25
        echo (isset($context["text_store"]) ? $context["text_store"] : null);
        echo "</li>
        </ol>
      </fieldset>
      <h3>";
        // line 28
        echo (isset($context["text_steps"]) ? $context["text_steps"] : null);
        echo "</h3>
      <fieldset>
        <div class=\"form-group\">
          <label class=\"col-sm-2 control-label\">";
        // line 31
        echo (isset($context["entry_progress"]) ? $context["entry_progress"] : null);
        echo "</label>
          <div class=\"col-sm-10\">
            <div class=\"progress\">
              <div id=\"progress-bar\" class=\"progress-bar\" style=\"width: 0%;\"></div>
            </div>
            <div id=\"progress-text\"></div>
          </div>
        </div>
      </fieldset>
      <div class=\"buttons\">
        <div class=\"text-right\">
          <input type=\"submit\" value=\"";
        // line 42
        echo (isset($context["button_continue"]) ? $context["button_continue"] : null);
        echo "\" id=\"button-continue\" class=\"btn btn-primary\" />
        </div>
      </div>
    </div>
    <div class=\"col-sm-3\">";
        // line 46
        echo (isset($context["column_left"]) ? $context["column_left"] : null);
        echo "</div>
  </div>
  <script type=\"text/javascript\"><!--
var step = 0;

\$('#button-continue').on('click', function() {
\t\$('#progress-bar').addClass('progress-bar-success').css('width', '0%').removeClass('progress-bar-danger');
\t\$('#progress-text').html('');
\t\$('#button-continue').prop('disabled', true).before('<i class=\"fa fa-spinner fa-spin\"></i> ');

\tstart('index.php?route=upgrade/upgrade/next');
});

function start(url) {
\tsetTimeout(function(){
\t\t\$.ajax({
\t\t\turl: url,
\t\t\ttype: 'post',
\t\t\tdataType: 'json',
\t\t\tsuccess: function(json) {
\t\t\t\tif (json['error']) {
\t\t\t\t\t\$('#progress-bar').addClass('progress-bar-danger');
\t\t\t\t\t\$('#progress-text').html('<div class=\"text-danger\">' + json['error'] + '</div>');

\t\t\t\t\t\$('#button-continue').prop('disabled', false);
\t\t\t\t\t\$('.fa-spinner').remove();
\t\t\t\t}

\t\t\t\tif (json['success']) {
\t\t\t\t\t\$('#progress-text').html('<span class=\"text-success\">' + json['success'] + '</span>');
\t\t\t\t\t\$('#progress-bar').css('width', ((step / ";
        // line 76
        echo (isset($context["total"]) ? $context["total"] : null);
        echo ") * 100) + '%');
\t\t\t\t}

\t\t\t\tif (json['next']) {
\t\t\t\t\tstart(json['next']);
\t\t\t\t} else if (!json['error']) {
\t\t\t\t\t\$('#button-continue').replaceWith('<a href=\"";
        // line 82
        echo (isset($context["store"]) ? $context["store"] : null);
        echo "\" class=\"btn btn-primary\">";
        echo (isset($context["button_continue"]) ? $context["button_continue"] : null);
        echo "</a>');
\t\t\t\t\t\$('.fa-spinner').remove();
\t\t\t\t}

\t\t\t\tstep++;
\t\t\t},
\t\t\terror: function(xhr, ajaxOptions, thrownError) {
\t\t\t\t\$('#progress-bar').addClass('progress-bar-danger');
\t\t\t\t\$('#progress-text').html('<div class=\"text-danger\">' + (thrownError + \"\\r\\n\" + xhr.statusText + \"\\r\\n\" + xhr.responseText) + '</div>');
\t\t\t\t\$('#button-continue').prop('disabled', false);
\t\t\t\t\$('.fa-spinner').remove();
\t\t\t}
\t\t});
\t}, 1000);
}
//--></script></div>
";
        // line 98
        echo (isset($context["footer"]) ? $context["footer"] : null);
    }

    public function getTemplateName()
    {
        return "upgrade/upgrade.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  166 => 98,  145 => 82,  136 => 76,  103 => 46,  96 => 42,  82 => 31,  76 => 28,  70 => 25,  66 => 24,  62 => 23,  58 => 22,  54 => 21,  50 => 20,  44 => 17,  32 => 8,  28 => 7,  19 => 1,);
    }
}
/* {{ header }}*/
/* <div class="container">*/
/*   <header>*/
/*     <div class="row">*/
/*       <div class="col-sm-6">*/
/*         <h1 class="pull-left">1<small>/2</small></h1>*/
/*         <h3>{{ heading_title }}<br>*/
/*           <small>{{ text_upgrade }}</small></h3>*/
/*       </div>*/
/*       <div class="col-sm-6">*/
/*         <div id="logo" class="pull-right hidden-xs"><img src="view/image/logo.png" alt="OpenCart" title="OpenCart" /></div>*/
/*       </div>*/
/*     </div>*/
/*   </header>*/
/*   <div class="row">*/
/*     <div class="col-sm-9">*/
/*       <h3>{{ text_server }}</h3>*/
/*       <fieldset>*/
/*         <ol>*/
/*           <li>{{ text_error }}</li>*/
/*           <li>{{ text_clear }}</li>*/
/*           <li>{{ text_admin }}</li>*/
/*           <li>{{ text_user }}</li>*/
/*           <li>{{ text_setting }}</li>*/
/*           <li>{{ text_store }}</li>*/
/*         </ol>*/
/*       </fieldset>*/
/*       <h3>{{ text_steps }}</h3>*/
/*       <fieldset>*/
/*         <div class="form-group">*/
/*           <label class="col-sm-2 control-label">{{ entry_progress }}</label>*/
/*           <div class="col-sm-10">*/
/*             <div class="progress">*/
/*               <div id="progress-bar" class="progress-bar" style="width: 0%;"></div>*/
/*             </div>*/
/*             <div id="progress-text"></div>*/
/*           </div>*/
/*         </div>*/
/*       </fieldset>*/
/*       <div class="buttons">*/
/*         <div class="text-right">*/
/*           <input type="submit" value="{{ button_continue }}" id="button-continue" class="btn btn-primary" />*/
/*         </div>*/
/*       </div>*/
/*     </div>*/
/*     <div class="col-sm-3">{{ column_left }}</div>*/
/*   </div>*/
/*   <script type="text/javascript"><!--*/
/* var step = 0;*/
/* */
/* $('#button-continue').on('click', function() {*/
/* 	$('#progress-bar').addClass('progress-bar-success').css('width', '0%').removeClass('progress-bar-danger');*/
/* 	$('#progress-text').html('');*/
/* 	$('#button-continue').prop('disabled', true).before('<i class="fa fa-spinner fa-spin"></i> ');*/
/* */
/* 	start('index.php?route=upgrade/upgrade/next');*/
/* });*/
/* */
/* function start(url) {*/
/* 	setTimeout(function(){*/
/* 		$.ajax({*/
/* 			url: url,*/
/* 			type: 'post',*/
/* 			dataType: 'json',*/
/* 			success: function(json) {*/
/* 				if (json['error']) {*/
/* 					$('#progress-bar').addClass('progress-bar-danger');*/
/* 					$('#progress-text').html('<div class="text-danger">' + json['error'] + '</div>');*/
/* */
/* 					$('#button-continue').prop('disabled', false);*/
/* 					$('.fa-spinner').remove();*/
/* 				}*/
/* */
/* 				if (json['success']) {*/
/* 					$('#progress-text').html('<span class="text-success">' + json['success'] + '</span>');*/
/* 					$('#progress-bar').css('width', ((step / {{ total }}) * 100) + '%');*/
/* 				}*/
/* */
/* 				if (json['next']) {*/
/* 					start(json['next']);*/
/* 				} else if (!json['error']) {*/
/* 					$('#button-continue').replaceWith('<a href="{{ store }}" class="btn btn-primary">{{ button_continue }}</a>');*/
/* 					$('.fa-spinner').remove();*/
/* 				}*/
/* */
/* 				step++;*/
/* 			},*/
/* 			error: function(xhr, ajaxOptions, thrownError) {*/
/* 				$('#progress-bar').addClass('progress-bar-danger');*/
/* 				$('#progress-text').html('<div class="text-danger">' + (thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText) + '</div>');*/
/* 				$('#button-continue').prop('disabled', false);*/
/* 				$('.fa-spinner').remove();*/
/* 			}*/
/* 		});*/
/* 	}, 1000);*/
/* }*/
/* //--></script></div>*/
/* {{ footer }}*/
