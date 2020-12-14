# Устранение неисправностей <a id="troubleshooting"></a>

## Несовместимость с модулем PageSpeed <a id="pagespeed-incompatibility"></a>

Похоже, что Web 2 не совместим с модулем PageSpeed. Пожалуйста, отключите модуль PageSpeed одним из
следующих способов.

**Apache**:
```
ModPagespeedDisallow "*/icingaweb2/*"
```

**Nginx**:
```
pagespeed Disallow "*/icingaweb2/*";
```
