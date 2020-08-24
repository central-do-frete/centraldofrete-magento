# Central do Frete - Magento 2

O presente repositório refere-se ao módulo de cotações de frete da Central do Frete para Magento 2.

# Compatibilidade

Esta versão do módulo é compatível com o Magento 2.2+.

# Instalação
Faça o download do repositório usando uma das seguintes alternativas:

## Usando o Composer (recomendado)
```composer require buzzmage/centraldofrete-magento:dev-master```

## Manualmente
Baixe o presente repositório e mova para o diretório *app/code/Buzz/CentralDoFrete*.

## Ativação
```
bin/magento module:enable Buzz_CentralDoFrete  // Realiza a ativação do módulo
bin/magento setup:upgrade                      // Executa o registro do módulo no Magento
bin/magento setup:di:compile                   // Recompila o projeto (necessário quando o Magento 2 se encontra em modo de produção)
```
# Configuração

Acesse Stores > Configuration > Sales > Shipping Methods > Central do Frete.

## Suporte
Em caso de problemas, por favor, envie-nos um e-mail (contato@sitedabuzz.com.br) com o máximo de informações possíveis acerca da ocorrência.
