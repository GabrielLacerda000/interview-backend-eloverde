# Fluxo: Pre-Execution Check

**Endpoint:** `POST /api/collect-tasks/pre-execution-check`  
**Propósito:** Receber uma lista de IDs de coletas, analisar cada uma contra um conjunto de regras de negócio, e retornar um relatório ordenado indicando se cada coleta pode ser executada.

---

## Diagrama

```
HTTP Request
    │
    ▼
PreExecutionCheckRequest   ← valida o JSON de entrada
    │
    ▼
CollectTaskController      ← recebe a requisição e chama a Action
    │
    ▼
PreExecutionCheckAction    ← busca as coletas no banco e orquestra
    │
    ├─► CollectTaskPreExecutionAnalyzer (uma vez por coleta)
    │       │
    │       ├─► InvalidStateChecker
    │       ├─► MissingRequiredDocumentsChecker
    │       ├─► ExpiredRequiredDocumentsChecker
    │       ├─► InvalidRequiredDocumentsChecker
    │       └─► DuplicateCollectTaskChecker
    │               │
    │               ▼
    │           CheckResult (blockers + relatedCollectTaskId)
    │               │
    │               ▼
    │       PreExecutionCheckResultDTO
    │
    ▼
PreExecutionCheckSorter    ← ordena a coleção de resultados
    │
    ▼
PreExecutionCheckResource  ← formata para JSON
    │
    ▼
HTTP Response
```

---

## Etapas

### 1. PreExecutionCheckRequest
**O que é:** Form Request do Laravel — valida o corpo da requisição antes de chegar no controller.

| | |
|---|---|
| **Recebe** | Corpo JSON da requisição HTTP |
| **Faz** | Garante que `collect_task_ids` é um array de inteiros únicos que existem na tabela `collect_tasks` |
| **Retorna** | Dados validados (ou aborta com HTTP 422 se inválido) |

**Arquivo:** `app/Domain/Waste/Http/Requests/PreExecutionCheckRequest.php`

---

### 2. CollectTaskController
**O que é:** Ponto de entrada HTTP — conecta a rota à lógica de negócio.

| | |
|---|---|
| **Recebe** | `PreExecutionCheckRequest` (já validado) |
| **Faz** | Extrai os IDs validados, chama `PreExecutionCheckAction::execute()`, e envolve o resultado em um Resource |
| **Retorna** | `AnonymousResourceCollection` → HTTP 200 com JSON |

**Arquivo:** `app/Domain/Waste/Http/Controllers/CollectTaskController.php`

---

### 3. PreExecutionCheckAction
**O que é:** Orquestrador central do fluxo — única classe que conhece todas as peças.

| | |
|---|---|
| **Recebe** | `Collection` de IDs de coletas |
| **Faz** | (1) Busca os `CollectTask` no banco com eager load dos relacionamentos necessários. (2) Instancia os checkers e o Analyzer. (3) Analisa cada coleta. (4) Passa a coleção pelo Sorter. |
| **Retorna** | `Collection<PreExecutionCheckResultDTO>` ordenada |

**Eager load realizado aqui:** `items`, `wasteGenerationPoint.documents.documentType`

**Arquivo:** `app/Domain/Waste/Actions/CollectTask/PreExecutionCheckAction.php`

---

### 4. CollectTaskPreExecutionAnalyzer
**O que é:** Analisador individual — recebe UMA coleta e devolve UM relatório.

| | |
|---|---|
| **Recebe** | Um `CollectTask` (com relacionamentos carregados) + array de checkers injetados no construtor |
| **Faz** | Executa todos os checkers, agrega os blockers retornados, captura o `relatedCollectTaskId` se houver, e deriva o `suggested_action` |
| **Retorna** | `PreExecutionCheckResultDTO` para aquela coleta |

**Regra de `suggested_action`:** ver tabela no final deste documento.

**Arquivo:** `app/Domain/Waste/Analyzers/CollectTask/CollectTaskPreExecutionAnalyzer.php`

---

### 5. Checkers
**O que são:** Classes especializadas, cada uma responsável por UMA regra de negócio. Todas implementam `CollectTaskCheckerInterface`.

| | |
|---|---|
| **Recebem** | Um `CollectTask` |
| **Fazem** | Verificam uma condição específica |
| **Retornam** | `CheckResult` — array de blockers encontrados (vazio se passou) + `relatedCollectTaskId` quando aplicável |

| Checker | Regra verificada |
|---|---|
| `InvalidStateChecker` | A coleta deve estar no estado `programming` |
| `MissingRequiredDocumentsChecker` | O ponto de geração deve ter um documento para cada `DocumentType` obrigatório |
| `ExpiredRequiredDocumentsChecker` | Nenhum documento obrigatório pode ter `expires_at` no passado |
| `InvalidRequiredDocumentsChecker` | Nenhum documento obrigatório pode ter `status = invalid` |
| `DuplicateCollectTaskChecker` | Não pode existir outra coleta no mesmo dia, mesmo ponto e mesmos resíduos |

> Os 4 primeiros checkers trabalham apenas com dados já carregados em memória.  
> O `DuplicateCollectTaskChecker` faz uma query extra ao banco para encontrar a coleta duplicada.

**Arquivos:** `app/Domain/Waste/Checkers/CollectTask/`

---

### 6. CheckResult
**O que é:** DTO de retorno de cada checker — estrutura simples sem lógica.

| Campo | Tipo | Descrição |
|---|---|---|
| `blockers` | `Blocker[]` | Enum cases dos bloqueadores encontrados (vazio se passou) |
| `relatedCollectTaskId` | `?int` | ID da coleta relacionada (apenas para duplicatas) |

**Arquivo:** `app/Domain/Waste/DTO/CollectTask/CheckResult.php`

---

### 7. PreExecutionCheckResultDTO
**O que é:** DTO do resultado final de análise de UMA coleta — carrega todos os dados necessários para a resposta e para a ordenação.

| Campo | Tipo | Exposto na resposta? |
|---|---|---|
| `collect_task_id` | `int` | Sim |
| `can_execute` | `bool` | Sim |
| `priority` | `string` (`high`/`normal`) | Sim |
| `blockers` | `string[]` | Sim |
| `suggested_action` | `string` | Sim |
| `related_collect_task_id` | `?int` | Sim |
| `scheduled_to` | `Carbon` | **Não** — usado apenas pelo Sorter |

**Arquivo:** `app/Domain/Waste/DTO/CollectTask/PreExecutionCheckResultDTO.php`

---

### 8. PreExecutionCheckSorter
**O que é:** Ordena a coleção de resultados de acordo com as regras de prioridade.

| | |
|---|---|
| **Recebe** | `Collection<PreExecutionCheckResultDTO>` (não ordenada) |
| **Faz** | Aplica ordenação multi-critério |
| **Retorna** | `Collection<PreExecutionCheckResultDTO>` ordenada |

**Critérios de ordenação (nesta ordem):**

1. `priority` — `high` antes de `normal`
2. `can_execute` — `false` (bloqueado) antes de `true` (elegível) dentro do mesmo nível
3. `scheduled_to` — mais antigo primeiro, como desempate

**Arquivo:** `app/Domain/Waste/Sorters/PreExecutionCheckSorter.php`

---

### 9. PreExecutionCheckResource
**O que é:** Laravel API Resource — transforma o DTO em JSON para a resposta HTTP.

| | |
|---|---|
| **Recebe** | `PreExecutionCheckResultDTO` |
| **Faz** | Mapeia explicitamente os campos públicos para o array de resposta (omite `scheduled_to`) |
| **Retorna** | Array que o Laravel serializa para JSON |

**Arquivo:** `app/Domain/Waste/Http/Resources/PreExecutionCheckResource.php`

---

## Referência rápida: suggested_action

| Blockers presentes | suggested_action |
|---|---|
| Nenhum | `execute` |
| Apenas `invalid_state` | `fix_state` |
| Apenas `missing_required_documents` e/ou `expired_required_documents` e/ou `invalid_required_documents` | `review_documents` |
| Apenas `duplicate_collect_for_same_day` | `review_or_merge` |
| Combinação de tipos diferentes | `manual_review` |

---

## Enum de Blockers

Todos os bloqueadores são valores do enum `App\Domain\Waste\Enums\Blocker`:

| Case | Valor (string no JSON) |
|---|---|
| `INVALID_STATE` | `invalid_state` |
| `MISSING_REQUIRED_DOCUMENTS` | `missing_required_documents` |
| `EXPIRED_REQUIRED_DOCUMENTS` | `expired_required_documents` |
| `INVALID_REQUIRED_DOCUMENTS` | `invalid_required_documents` |
| `DUPLICATE_COLLECT_FOR_SAME_DAY` | `duplicate_collect_for_same_day` |
