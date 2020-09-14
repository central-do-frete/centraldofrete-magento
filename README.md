# Central do Frete - Magento 2
O presente repositório refere-se ao módulo de cotações de frete da Central do Frete para Magento 2.

# Compatibilidade
Esta versão do módulo é compatível com o Magento 2.2+.

# Instalação
Faça o download do repositório usando uma das seguintes alternativas:

### Usando o Composer (recomendado)
```composer require buzzmage/centraldofrete-magento:dev-master```

### Manualmente
Baixe o presente repositório e mova para o diretório *app/code/Buzz/CentralDoFrete*.

### Ativação
```
bin/magento module:enable Buzz_CentralDoFrete  // Realiza a ativação do módulo
bin/magento setup:upgrade                      // Executa o registro do módulo no Magento
bin/magento setup:di:compile                   // Recompila o projeto (necessário quando o Magento 2 se encontra em modo de produção)
```
# Configuração
Acesse Stores > Configuration > Sales > Shipping Methods > Central do Frete.

Para o correto funcionamento do módulo, realize as configurações de token, mapeamento de atributos e definição das medidas padrão.

Caso ainda não tenha o token, faça o cadastro em https://app.centraldofrete.com/cadastro

![Configuração](https://i.imgur.com/iyDygPo.png)
![Configuração](https://i.imgur.com/FfbE9Nw.png)

# Uso
Atenção nas configurações do módulo.

## Tipos de Carga

Após o salvamento das configurações do módulo, com o token válido, os tipos de carga serão baixados e estarão disponíveis nas configurações do produto e do módulo da Central do Frete. Caso o tipo de garga não esteja selecionado no produto, o módulo optará por enviar na cotação o tipo padrão selecionado nas configurações padrão do módulo. 

![Seleção do tipo de carga nas configurações do módulo](https://i.imgur.com/Garv45Q.png)

*Seleção do tipo de carga nas configurações do módulo*

![Seleção do tipo de carga nas configurações do produto](https://i.imgur.com/XW5xAhD.png)

*Seleção do tipo de carga nas configurações do produto*


## Código da Cotação de Frete 
Para visualizar o código da cotação do frete, abra a visualização de pedido no painel administrativo da sua loja, como imagem abaixo.

![Configuração](https://i.imgur.com/ljYQlv3.png)

# Suporte
Em caso de problemas, por favor, envie-nos um e-mail (contato@sitedabuzz.com.br) com o máximo de informações possíveis acerca da ocorrência.
