---
name: create-pull-request
description: Create a GitHub pull request for completed work in this repository, following its Issue, acceptance-criteria, verification, and screenshot conventions. Use when asked to open or create a pull request.
---

# Create a pull request

Create a reviewable PR for the requested completed work. Read `AGENTS.md` first and follow its durable repository rules. This Skill supplies the PR workflow and body format.

## Before creating the PR

1. Confirm the current branch, its base branch, and the associated Issue. Repository convention is one Issue per branch and PR.
2. Inspect `git status`, the commits, and the complete diff against the base. Keep unrelated user changes out of the PR. Do not commit changes unless asked.
3. Read the Issue, relevant design documents, and any Tutorial references needed to report the scope and acceptance criteria accurately.
4. Check the changes and existing verification results. Run checks only when the user requested them or they are necessary for the requested PR work. Report checks that were not run and why.
5. If the change affects a screen, locate the relevant screenshots for inclusion in the PR. Do not claim screenshots are attached when none are available.
6. Confirm the intended base branch and whether an open PR already exists for this branch. For an existing PR, update it only when the user asked to create/update the PR; make the title and body reflect the full current PR scope.

## Prepare the PR

Use this body structure and fill each section with concrete information:

```markdown
## 概要
<!-- 何を変更したかを短く記載 -->

## 関連Issue・Tutorial
- Issue: #番号 または 該当なし
- Tutorial: 節番号・名称 または 該当なし

## 変更内容
-

## 受け入れ条件
- [ ] AC-番号: 条件 — 結果（完了 / 未完了 / 対象外）

## 確認
- 実行したコマンド・手動確認と結果
- 未実施の場合は、その理由

## スクリーンショット
<!-- 画面変更がある場合に添付。なければ「該当なし」 -->
```

- Preserve acceptance-criteria wording from the Issue or design source. State the result for each criterion; do not imply that an unverified criterion passed.
- Include the relevant Tutorial section and Issue, or state `該当なし`.
- For an Issue PR, end the body with `Closes #<イシュー番号>`.
- Include screenshot links or attachments when the change affects a screen.
- Include additional requested context, such as planning choices, implementation rationale, design-document changes, or deliberately omitted work, when the user asks for it.

## Create and report

- Push the PR branch and create the PR only when the user asked for the PR. Do not merge or delete the branch unless separately requested.
- Use the repository's configured remote and base branch. Prefer `gh pr create` with a body file so Markdown formatting is preserved.
- After creation, inspect the PR URL, title, base/head, and body with `gh pr view`; report the URL and any checks or review items that remain.
- If authentication, permissions, remote state, or an unresolved scope decision prevents safe completion, stop and report the specific blocker rather than changing remotes or rewriting history as a workaround.
