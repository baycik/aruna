

ROBOCOPY "../../catalog\view\javascript\aruna" "./upload/catalog\view\javascript\aruna" /mir
ROBOCOPY "../../catalog\view\theme\default\template\mail" "./upload/catalog\view\theme\default\template\mail" "order_alert.twig" /mir
ROBOCOPY "../../catalog\view\theme\default\template\extension\aruna" "./upload/catalog\view\theme\default\template\extension\aruna" "cart.twig" /mir
ROBOCOPY "../../catalog\view\theme\default\template\extension\module" "./upload/catalog\view\theme\default\template\extension\module" "purpletree_sellerpanel.twig /mir
ROBOCOPY "../../catalog\view\theme\so-emarket\template\header" "./upload/catalog\view\theme\so-emarket\template\header" "header17.twig" /mir
ROBOCOPY "../../catalog\view\theme\so-emarket\template\information" "./upload/catalog\view\theme\so-emarket\template\information" "contact.twig" /mir
ROBOCOPY "../../catalog\view\theme\so-mobile\template\soconfig" "./upload/catalog\view\theme\so-mobile\template\soconfig" "panel_left.twig" /mir


"%ProgramFiles%\WinRAR\WinRAR.exe" a -afzip -ep1 -ibck -r -y -x*.bat -x*.zip "../../../ArunaCustom.ocmod.zip" "./*"

PAUSE PAUSE PAUSE PAUSE