<?php

/* default/template/account/account.twig */
class __TwigTemplate_4a412419f58abaff8274ceafa5a6f0962ac88fb53bffcc7bc9022e3f7da32b3d extends Twig_Template
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
<div id=\"account-account\" class=\"container\">
  <ul class=\"breadcrumb\">
    ";
        // line 4
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["breadcrumbs"]) ? $context["breadcrumbs"] : null));
        foreach ($context['_seq'] as $context["_key"] => $context["breadcrumb"]) {
            // line 5
            echo "    <li><a href=\"";
            echo $this->getAttribute($context["breadcrumb"], "href", array());
            echo "\">";
            echo $this->getAttribute($context["breadcrumb"], "text", array());
            echo "</a></li>
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['breadcrumb'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 7
        echo "  </ul>
  ";
        // line 8
        if ((isset($context["success"]) ? $context["success"] : null)) {
            // line 9
            echo "  <div class=\"alert alert-success alert-dismissible\"><i class=\"fa fa-check-circle\"></i> ";
            echo (isset($context["success"]) ? $context["success"] : null);
            echo "</div>
  ";
        }
        // line 11
        echo "  <div class=\"row\">";
        echo (isset($context["column_left"]) ? $context["column_left"] : null);
        echo "
    ";
        // line 12
        if (((isset($context["column_left"]) ? $context["column_left"] : null) && (isset($context["column_right"]) ? $context["column_right"] : null))) {
            // line 13
            echo "    ";
            $context["class"] = "col-sm-6";
            // line 14
            echo "    ";
        } elseif (((isset($context["column_left"]) ? $context["column_left"] : null) || (isset($context["column_right"]) ? $context["column_right"] : null))) {
            // line 15
            echo "    ";
            $context["class"] = "col-sm-9";
            // line 16
            echo "    ";
        } else {
            // line 17
            echo "    ";
            $context["class"] = "col-sm-12";
            // line 18
            echo "    ";
        }
        // line 19
        echo "    <div id=\"content\" class=\"";
        echo (isset($context["class"]) ? $context["class"] : null);
        echo "\">";
        echo (isset($context["content_top"]) ? $context["content_top"] : null);
        echo "
      <h2>";
        // line 20
        echo (isset($context["text_my_account"]) ? $context["text_my_account"] : null);
        echo "</h2>
      <ul class=\"list-unstyled\">
        <li><a href=\"";
        // line 22
        echo (isset($context["edit"]) ? $context["edit"] : null);
        echo "\">";
        echo (isset($context["text_edit"]) ? $context["text_edit"] : null);
        echo "</a></li>
        <li><a href=\"";
        // line 23
        echo (isset($context["password"]) ? $context["password"] : null);
        echo "\">";
        echo (isset($context["text_password"]) ? $context["text_password"] : null);
        echo "</a></li>
        <li><a href=\"";
        // line 24
        echo (isset($context["address"]) ? $context["address"] : null);
        echo "\">";
        echo (isset($context["text_address"]) ? $context["text_address"] : null);
        echo "</a></li>
        <li><a href=\"";
        // line 25
        echo (isset($context["wishlist"]) ? $context["wishlist"] : null);
        echo "\">";
        echo (isset($context["text_wishlist"]) ? $context["text_wishlist"] : null);
        echo "</a></li>
      </ul>
      ";
        // line 27
        if ((isset($context["credit_cards"]) ? $context["credit_cards"] : null)) {
            // line 28
            echo "      <h2>";
            echo (isset($context["text_credit_card"]) ? $context["text_credit_card"] : null);
            echo "</h2>
      <ul class=\"list-unstyled\">
        ";
            // line 30
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable((isset($context["credit_cards"]) ? $context["credit_cards"] : null));
            foreach ($context['_seq'] as $context["_key"] => $context["credit_card"]) {
                // line 31
                echo "        <li><a href=\"";
                echo $this->getAttribute($context["credit_card"], "href", array());
                echo "\">";
                echo $this->getAttribute($context["credit_card"], "name", array());
                echo "</a></li>
        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['credit_card'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 33
            echo "      </ul>
      ";
        }
        // line 35
        echo "      <h2>";
        echo (isset($context["text_my_orders"]) ? $context["text_my_orders"] : null);
        echo "</h2>
      <ul class=\"list-unstyled\">
        <li><a href=\"";
        // line 37
        echo (isset($context["order"]) ? $context["order"] : null);
        echo "\">";
        echo (isset($context["text_order"]) ? $context["text_order"] : null);
        echo "</a></li>
        <li><a href=\"";
        // line 38
        echo (isset($context["download"]) ? $context["download"] : null);
        echo "\">";
        echo (isset($context["text_download"]) ? $context["text_download"] : null);
        echo "</a></li>
        ";
        // line 39
        if ((isset($context["reward"]) ? $context["reward"] : null)) {
            // line 40
            echo "        <li><a href=\"";
            echo (isset($context["reward"]) ? $context["reward"] : null);
            echo "\">";
            echo (isset($context["text_reward"]) ? $context["text_reward"] : null);
            echo "</a></li>
        ";
        }
        // line 42
        echo "        <li><a href=\"";
        echo (isset($context["return"]) ? $context["return"] : null);
        echo "\">";
        echo (isset($context["text_return"]) ? $context["text_return"] : null);
        echo "</a></li>
        <li><a href=\"";
        // line 43
        echo (isset($context["transaction"]) ? $context["transaction"] : null);
        echo "\">";
        echo (isset($context["text_transaction"]) ? $context["text_transaction"] : null);
        echo "</a></li>
        <li><a href=\"";
        // line 44
        echo (isset($context["recurring"]) ? $context["recurring"] : null);
        echo "\">";
        echo (isset($context["text_recurring"]) ? $context["text_recurring"] : null);
        echo "</a></li>
      </ul>
      <h2>";
        // line 46
        echo (isset($context["text_my_affiliate"]) ? $context["text_my_affiliate"] : null);
        echo "</h2>
      <ul class=\"list-unstyled\">
        ";
        // line 48
        if ( !(isset($context["tracking"]) ? $context["tracking"] : null)) {
            // line 49
            echo "        <li><a href=\"";
            echo (isset($context["affiliate"]) ? $context["affiliate"] : null);
            echo "\">";
            echo (isset($context["text_affiliate_add"]) ? $context["text_affiliate_add"] : null);
            echo "</a></li>
        ";
        } else {
            // line 51
            echo "        <li><a href=\"";
            echo (isset($context["affiliate"]) ? $context["affiliate"] : null);
            echo "\">";
            echo (isset($context["text_affiliate_edit"]) ? $context["text_affiliate_edit"] : null);
            echo "</a></li>
        <li><a href=\"";
            // line 52
            echo (isset($context["tracking"]) ? $context["tracking"] : null);
            echo "\">";
            echo (isset($context["text_tracking"]) ? $context["text_tracking"] : null);
            echo "</a></li>
        ";
        }
        // line 54
        echo "      </ul>
      <h2>";
        // line 55
        echo (isset($context["text_my_newsletter"]) ? $context["text_my_newsletter"] : null);
        echo "</h2>
      <ul class=\"list-unstyled\">
        <li><a href=\"";
        // line 57
        echo (isset($context["newsletter"]) ? $context["newsletter"] : null);
        echo "\">";
        echo (isset($context["text_newsletter"]) ? $context["text_newsletter"] : null);
        echo "</a></li>
      </ul>
      ";
        // line 59
        echo (isset($context["content_bottom"]) ? $context["content_bottom"] : null);
        echo "</div>
    ";
        // line 60
        echo (isset($context["column_right"]) ? $context["column_right"] : null);
        echo "</div>
</div>
";
        // line 62
        echo (isset($context["footer"]) ? $context["footer"] : null);
    }

    public function getTemplateName()
    {
        return "default/template/account/account.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  241 => 62,  236 => 60,  232 => 59,  225 => 57,  220 => 55,  217 => 54,  210 => 52,  203 => 51,  195 => 49,  193 => 48,  188 => 46,  181 => 44,  175 => 43,  168 => 42,  160 => 40,  158 => 39,  152 => 38,  146 => 37,  140 => 35,  136 => 33,  125 => 31,  121 => 30,  115 => 28,  113 => 27,  106 => 25,  100 => 24,  94 => 23,  88 => 22,  83 => 20,  76 => 19,  73 => 18,  70 => 17,  67 => 16,  64 => 15,  61 => 14,  58 => 13,  56 => 12,  51 => 11,  45 => 9,  43 => 8,  40 => 7,  29 => 5,  25 => 4,  19 => 1,);
    }
}
/* {{ header }}*/
/* <div id="account-account" class="container">*/
/*   <ul class="breadcrumb">*/
/*     {% for breadcrumb in breadcrumbs %}*/
/*     <li><a href="{{ breadcrumb.href }}">{{ breadcrumb.text }}</a></li>*/
/*     {% endfor %}*/
/*   </ul>*/
/*   {% if success %}*/
/*   <div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> {{ success }}</div>*/
/*   {% endif %}*/
/*   <div class="row">{{ column_left }}*/
/*     {% if column_left and column_right %}*/
/*     {% set class = 'col-sm-6' %}*/
/*     {% elseif column_left or column_right %}*/
/*     {% set class = 'col-sm-9' %}*/
/*     {% else %}*/
/*     {% set class = 'col-sm-12' %}*/
/*     {% endif %}*/
/*     <div id="content" class="{{ class }}">{{ content_top }}*/
/*       <h2>{{ text_my_account }}</h2>*/
/*       <ul class="list-unstyled">*/
/*         <li><a href="{{ edit }}">{{ text_edit }}</a></li>*/
/*         <li><a href="{{ password }}">{{ text_password }}</a></li>*/
/*         <li><a href="{{ address }}">{{ text_address }}</a></li>*/
/*         <li><a href="{{ wishlist }}">{{ text_wishlist }}</a></li>*/
/*       </ul>*/
/*       {% if credit_cards %}*/
/*       <h2>{{ text_credit_card }}</h2>*/
/*       <ul class="list-unstyled">*/
/*         {% for credit_card in credit_cards %}*/
/*         <li><a href="{{ credit_card.href }}">{{ credit_card.name }}</a></li>*/
/*         {% endfor %}*/
/*       </ul>*/
/*       {% endif %}*/
/*       <h2>{{ text_my_orders }}</h2>*/
/*       <ul class="list-unstyled">*/
/*         <li><a href="{{ order }}">{{ text_order }}</a></li>*/
/*         <li><a href="{{ download }}">{{ text_download }}</a></li>*/
/*         {% if reward %}*/
/*         <li><a href="{{ reward }}">{{ text_reward }}</a></li>*/
/*         {% endif %}*/
/*         <li><a href="{{ return }}">{{ text_return }}</a></li>*/
/*         <li><a href="{{ transaction }}">{{ text_transaction }}</a></li>*/
/*         <li><a href="{{ recurring }}">{{ text_recurring }}</a></li>*/
/*       </ul>*/
/*       <h2>{{ text_my_affiliate }}</h2>*/
/*       <ul class="list-unstyled">*/
/*         {% if not tracking %}*/
/*         <li><a href="{{ affiliate }}">{{ text_affiliate_add }}</a></li>*/
/*         {% else %}*/
/*         <li><a href="{{ affiliate }}">{{ text_affiliate_edit }}</a></li>*/
/*         <li><a href="{{ tracking }}">{{ text_tracking }}</a></li>*/
/*         {% endif %}*/
/*       </ul>*/
/*       <h2>{{ text_my_newsletter }}</h2>*/
/*       <ul class="list-unstyled">*/
/*         <li><a href="{{ newsletter }}">{{ text_newsletter }}</a></li>*/
/*       </ul>*/
/*       {{ content_bottom }}</div>*/
/*     {{ column_right }}</div>*/
/* </div>*/
/* {{ footer }}*/
