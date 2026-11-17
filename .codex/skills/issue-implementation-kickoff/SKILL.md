---
name: issue-implementation-kickoff
description: Prepare a repository Issue for implementation by syncing its branch, reading relevant design materials, resolving blockers, and presenting a concrete plan before code changes.
---

# Issue implementation kickoff

Use this skill when work is starting on an existing Issue and the user wants the implementation branch and plan prepared before coding. It covers kickoff through the point where implementation can safely begin; it does not create Issues, review diffs, commit, push, or open a PR unless separately requested.

## Workflow

1. **Identify the target and preserve the workspace.** Confirm the repository, Issue number, current branch, and `git status`. Read `AGENTS.md` and the repository's branch instructions. Do not discard, overwrite, or hide existing changes. If unrelated or unclear working-tree changes could be affected by switching branches, stop and ask how to handle them.
2. **Prepare the Issue branch.** For a clean workspace, fetch `origin/main`, switch to `main`, update it with `git pull --ff-only origin main`, then create one branch for the Issue (for example, `issue/12-channel-members`). If the branch already exists, inspect its state and reuse it when clearly appropriate; do not reset it. Do not push merely because the branch was created.
3. **Read the source materials before planning.** Read `README.md`, `docs/spec.md`, `docs/design/README.md`, and the Issue-related `docs/design/*.md`. Check the Issue's exact acceptance criteria. For visual work, inspect the relevant `mockup/` pages and design guide. Check `docs/design/questions.md` for prior answers, recorded decisions, and unresolved items.
4. **Set the scope from evidence.** Map the acceptance criteria to the behavior and code areas involved. Identify required design updates, relevant existing implementation patterns, and work that belongs to another Issue. Do not expand the Issue to neighboring features just because they are prerequisites or convenient.
5. **Resolve blockers before editing.** Separate decisions already answered in the Issue, design notes, repository instructions, and conversation from decisions that still affect user-visible behavior, data, permissions, or compatibility. Ask concise, concrete questions only for unresolved decisions that materially change the implementation. Include the impact and viable choices. Continue independent read-only investigation while waiting, but do not make code changes that depend on the answer. Do not ask for confirmation when the available materials already determine the choice.
6. **Present the plan.** Before code changes, give a compact plan that states:
   - the Issue and prepared branch;
   - the acceptance criteria and scope to implement;
   - the implementation sequence and likely files or layers;
   - decisions made, their source, and why the chosen approach fits;
   - design documents that need updating and why;
   - explicitly excluded work;
   - relevant checks to run, following repository instructions and the user's request.
7. **Hand off to implementation.** If the user asked only for a plan, stop after presenting it. If they asked to plan and implement, start implementation once all material decisions are resolved; do not add an extra approval step. Update design documents before code whenever implementation requires a new or changed design decision.

## Repository-specific practices

- Follow `AGENTS.md` and the linked design documents as the source of truth. If the specification, design, mockup, and recorded answers conflict, explain the conflict and ask before choosing.
- Keep one Issue on one branch. Do not commit, push, or create a PR unless the user requests it.
- Treat tests, builds, and formatters according to `AGENTS.md` and the user's request; report what was run and what remains unchecked.
- Keep the plan proportional to the Issue. Do not create a separate plan file unless the user or repository workflow asks for one.
