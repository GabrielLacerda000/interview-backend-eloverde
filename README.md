# Interview Backend Eloverde

## Contexto
Este repositório representa um desafio técnico backend inspirado em um fluxo operacional da EloVerde.

O sistema possui coletas de resíduos agendadas para pontos geradores. Antes que uma coleta siga para execução, a API deve verificar se ela está apta do ponto de vista operacional.

O foco do desafio é implementar a checagem de pré-execução de coletas, avaliando estado, documentação obrigatória e outros impeditivos relevantes.

## Objetivo do desafio
Seu objetivo é completar a funcionalidade de `pre-execution-check`.

Para cada coleta recebida na entrada, a API deve determinar:

- se a coleta pode ser executada
- quais bloqueios foram encontrados
- qual ação sugerida faz mais sentido
- em que ordem os resultados devem ser retornados

## O que já existe no projeto
Este repositório já entrega uma base inicial funcional para que você foque na resolução do problema de negócio. O objetivo deste desafio não é avaliar sua capacidade de configurar um projeto do zero, mas sim como você interpreta requisitos, organiza a implementação, trabalha com testes e toma decisões técnicas em cima de um domínio já parcialmente estruturado.

### Base entregue
- Estrutura Laravel básica já configurada.
- Migrations e models mínimos já criados.
- Seed com cenários já preparados.
- Endpoint principal já roteado.
- Request e Resource já criados.
- Testes automatizados iniciais já escritos.
- Implementação principal incompleta.

Em outras palavras: a base do projeto já está montada, mas a parte mais importante da regra de negócio continua em aberto para ser construída.

## O que você deve fazer
- Implementar a regra principal de `pre-execution-check`.
- Fazer os testes existentes passarem.
- Manter o código organizado, legível e com responsabilidades bem separadas.
- Usar a base já existente como ponto de partida, sem reinventar a arquitetura do projeto.
- Complementar a cobertura de testes se identificar uma lacuna relevante.

Você não precisa reinventar a arquitetura do projeto. O objetivo é evoluir a base existente com uma implementação clara, correta e fácil de manter.

## O que esperamos de você
Durante a entrevista, queremos que você desenvolva o raciocínio junto com a gente, explicando suas decisões técnicas ao longo do processo.

O projeto será disponibilizado com antecedência para que você possa se familiarizar com o contexto, preparar o ambiente e chegar com tudo pronto para começar a codar.

O uso de IA na resolução do código está liberado, não tem nenhum problema e é inclusive incentivado. Queremos ver como você utiliza IA no dia a dia e como ela se integra ao seu fluxo de trabalho.

Não é obrigatório concluir todo o desafio durante a entrevista. O principal objetivo é entender como você programa no cotidiano, como estrutura demandas e como organiza seu processo de desenvolvimento.

## Regras de negócio
A verificação de pré-execução deve simular uma análise operacional antes que uma coleta seja enviada para execução. O objetivo é impedir que uma coleta siga adiante quando houver inconsistências importantes de estado, documentação ou duplicidade.

### Regras obrigatórias
- A coleta só pode ser considerada apta para execução quando estiver no estado `programming`.
- Se a coleta estiver em qualquer estado diferente de `programming`, ela deve ser retornada como bloqueada com o bloqueio `invalid_state`.
- Se faltar qualquer documento obrigatório do ponto gerador, a coleta deve ser bloqueada com o bloqueio `missing_required_documents`.
- Se um documento obrigatório existir, mas estiver vencido, a coleta deve ser bloqueada com o bloqueio `expired_required_documents`.
- Se um documento obrigatório existir, mas estiver com status inválido, a coleta deve ser bloqueada com o bloqueio `invalid_required_documents`.
- Se existir outra coleta no mesmo dia, para o mesmo ponto gerador e com o mesmo conjunto de resíduos, a API deve sinalizar duplicidade com o bloqueio `duplicate_collect_for_same_day`.
- A ordem dos resíduos não deve alterar a comparação de duplicidade.
- A própria coleta analisada nunca deve ser considerada duplicada de si mesma.
- Quando houver mais de uma duplicidade possível, a API pode retornar a coleta relacionada mais antiga.
- Coletas urgentes devem aparecer primeiro no retorno.
- Dentro do mesmo nível de urgência, coletas bloqueadas devem aparecer antes das aptas.
- Persistindo empate, a ordenação deve considerar `scheduled_to` em ordem crescente.
- O retorno precisa explicar os bloqueios encontrados.
- A API deve indicar uma ação sugerida coerente com o problema encontrado.
- A análise deve ser feita individualmente por coleta, mesmo quando a entrada tiver múltiplos IDs.

Uma mesma coleta pode apresentar mais de um problema ao mesmo tempo. Nesses casos, a API deve retornar todos os bloqueios identificados para aquela coleta, em vez de interromper a análise no primeiro erro encontrado.

### Ações sugeridas esperadas
- `execute`: quando a coleta não possuir bloqueios
- `review_documents`: quando os bloqueios forem apenas documentais
- `fix_state`: quando o problema for apenas estado inválido
- `review_or_merge`: quando houver duplicidade
- `manual_review`: quando houver múltiplos bloqueios de naturezas diferentes

## Contrato esperado do endpoint
### Request
`POST /api/collect-tasks/pre-execution-check`

```json
{
  "collect_task_ids": [1, 2, 9]
}
```

### Response esperada
```json
{
  "data": [
    {
      "collect_task_id": 1,
      "can_execute": true,
      "priority": "normal",
      "blockers": [],
      "suggested_action": "execute",
      "related_collect_task_id": null
    },
    {
      "collect_task_id": 2,
      "can_execute": false,
      "priority": "normal",
      "blockers": [
        "missing_required_documents"
      ],
      "suggested_action": "review_documents",
      "related_collect_task_id": null
    },
    {
      "collect_task_id": 9,
      "can_execute": false,
      "priority": "high",
      "blockers": [
        "invalid_required_documents"
      ],
      "suggested_action": "review_documents",
      "related_collect_task_id": null
    }
  ]
}
```

## Como rodar o projeto
### Pré-requisitos
Antes de começar, certifique-se de ter instalado:

- PHP 8.3+
- Composer
- SQLite 3

### Instalação
```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
```

### Configuração do ambiente
O projeto foi pensado para rodar localmente com SQLite, sem dependências externas.

O `.env.example` já está preparado para isso. Depois de copiar o arquivo, garanta que os valores principais estejam assim:

```env
APP_NAME="Interview Backend Eloverde"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

### Criar estrutura do banco e popular dados
```bash
php artisan migrate:fresh --seed
```

Esse comando deve:

- criar as tabelas do desafio
- executar o `InterviewChallengeSeeder`
- deixar o banco pronto com os cenários usados pelos testes e pela exploração manual da API

### Subir a API localmente
```bash
php artisan serve
```

Depois disso, a API ficará disponível em:

`http://localhost:8000`

### Rodar os testes
Para executar toda a suíte:

```bash
php artisan test
```

Para rodar apenas os testes do desafio:

```bash
php artisan test tests/Feature/CollectTask/PreExecutionCheckEndpointTest.php
php artisan test tests/Unit/Waste/CollectTask/PreExecutionCheckActionTest.php
```

### Fluxo recomendado para iniciar
1. Instale as dependências.
2. Copie o `.env`.
3. Gere a chave da aplicação.
4. Crie o arquivo SQLite.
5. Rode `php artisan migrate:fresh --seed`.
6. Rode `php artisan test`.
7. Comece a implementação principal.

## Testes
O projeto já contém testes automatizados que cobrem parte importante do comportamento esperado. Eles foram incluídos para orientar a implementação inicial e reduzir ambiguidades no entendimento do contrato da API.

Ao mesmo tempo, nem todas as regras do desafio estão completamente cobertas pelos testes existentes. Parte da proposta é justamente identificar lacunas relevantes de cobertura e decidir se faz sentido complementar essa cobertura durante a solução.

### Arquivos de teste existentes
- `tests/Feature/CollectTask/PreExecutionCheckEndpointTest.php`
- `tests/Unit/Waste/CollectTask/PreExecutionCheckActionTest.php`

### O que já está coberto
Os testes iniciais já cobrem:

- coleta apta para execução quando estiver em `programming` e sem pendências documentais
- bloqueio por estado inválido
- bloqueio por documento obrigatório ausente
- bloqueio por documento obrigatório vencido
- bloqueio por documento obrigatório com status inválido
- retorno de múltiplos bloqueios para a mesma coleta
- ordenação priorizando coletas urgentes e bloqueadas
- ação sugerida coerente com o tipo de problema encontrado

### O que não está totalmente coberto de propósito
Existe uma regra de negócio importante intencionalmente deixada sem cobertura inicial:

- detecção de duplicidade de coleta no mesmo dia, para o mesmo ponto gerador e com o mesmo conjunto de resíduos
- escolha da coleta relacionada mais antiga quando houver duplicidade

Além de fazer os testes existentes passarem, espera-se que você identifique essa lacuna e adicione pelo menos um teste automatizado que ajude a proteger essa regra.

## Critérios de avaliação
Durante a avaliação, será observado:

- clareza de modelagem
- separação de responsabilidades
- qualidade dos testes
- cuidado com edge cases
- legibilidade do código
- capacidade de comunicar decisões técnicas
- uso de IA no fluxo de desenvolvimento

## Bônus opcionais
Os itens abaixo não são obrigatórios, mas podem enriquecer a solução:

- melhorar as mensagens de bloqueio
- adicionar enum para estados e bloqueios
- refinar a estratégia de comparação de resíduos duplicados
- adicionar testes extras relevantes
