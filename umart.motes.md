site        =>              components/com_easyshop/
admin       =>              administrator/components/com_easyshop/

easyshop => umart
com_easyshop => com_umart
/libraries/easyshop => /libraries/umart
media/ => /media/com_umart/

## Affetcted DB tables

### EasyShop uninstalled
- action_logs [LIKE %% easyshop]
- assets [LIKE %% easyshop]

### Umart uninstalled
- action_logs [LIKE %% umart]
- assets [LIKE %% umart]
- content_types [LIKE %% umart]
