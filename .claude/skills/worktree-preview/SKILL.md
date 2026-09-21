---
name: worktree-preview
description: Workflow for developing a branch in an isolated git worktree while previewing it in DDEV, which only ever serves /site in this repo's main checkout. Use when starting isolated work on a new component/feature branch, or when asked to set up/use a git worktree in this project.
---

# Worktree + DDEV preview workflow

DDEV only serves one folder in this repo: `/site` (the main checkout). A git worktree checkout lives in a **separate** folder, so DDEV never sees it — that's why changes made in a worktree don't show up in the browser until you swap the branch into `/site`.

Git rule this workflow relies on: **a branch can only be checked out in one location at a time** (either a worktree, or the main `/site` checkout — never both). `git worktree remove` never deletes anything — no branch, commits, or history are lost. It just frees the branch so `/site` can check it out.

## Workflow

1. **Isolated work.** Create a worktree under `.claude/worktrees/<name>` on its own branch (convention: `component/<name>` for new blocks/components). Commit progress there as you go — DDEV won't reflect these changes yet.

   ```bash
   git worktree add .claude/worktrees/<name> -b component/<name> <base-branch-or-ref>
   ```

2. **Preview in the browser.** Free the branch from the worktree, then check it out where DDEV actually serves from:

   ```bash
   git worktree remove .claude/worktrees/<name>
   cd site && git checkout component/<name>
   ```

   `ddev start` + `npm run dev` already running in `/site` → the browser updates immediately, no restart needed.

3. **Preview, tweak, commit, push, open PR** — all from `/site` as normal, same as any other branch.

4. **Resume isolated work later.** Put `/site` back on whatever it was on before, then recreate the worktree from the branch (now just a normal branch ref, no `-b` needed):

   ```bash
   cd site && git checkout main   # or whatever /site was on before
   git worktree add .claude/worktrees/<name> component/<name>
   ```

## Notes

- Only one branch worked on this way can be live-previewable at a time — if you're juggling multiple worktree branches, only the one currently checked out in `/site` is visible in DDEV; the others sit untouched in their worktree folders until swapped in.
- Before checking out a different branch into `/site` (step 2 or 4), make sure `/site`'s current branch has no uncommitted changes you still need — `git status` first.
- This workflow is unrelated to which base branch/ref a new component branch starts from — check the relevant Trello card / task context for that; it's not always `main` (e.g. if a needed convention has landed on another branch first but hasn't been merged to `main` yet).
