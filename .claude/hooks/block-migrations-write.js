#!/usr/bin/env node
// PreToolUse hook: blocks Write/Edit/NotebookEdit and Bash writes targeting any "migrations" directory.

let raw = "";
process.stdin.on("data", (chunk) => (raw += chunk));
process.stdin.on("end", () => {
  let input;
  try {
    input = JSON.parse(raw || "{}");
  } catch {
    process.exit(0); // malformed input, don't block
  }

  const toolName = input.tool_name || "";
  const toolInput = input.tool_input || {};

  const pathHitsMigrations = (p) => {
    if (!p || typeof p !== "string") return false;
    return p
      .replace(/\\/g, "/")
      .split("/")
      .some((segment) => segment.toLowerCase() === "migrations");
  };

  let blocked = false;
  let reason = "";

  if (toolName === "Write" || toolName === "Edit") {
    if (pathHitsMigrations(toolInput.file_path)) {
      blocked = true;
      reason = `Blocked: ${toolName} into a "migrations" directory is not allowed (${toolInput.file_path}).`;
    }
  } else if (toolName === "NotebookEdit") {
    const p = toolInput.notebook_path || toolInput.file_path;
    if (pathHitsMigrations(p)) {
      blocked = true;
      reason = `Blocked: NotebookEdit into a "migrations" directory is not allowed (${p}).`;
    }
  } else if (toolName === "Bash") {
    const cmd = toolInput.command || "";

    // Laravel/Artisan always writes a new file into database/migrations.
    const makesMigration = /\bmake:migration\b/i.test(cmd);

    // Generic file-write indicators.
    const writeIndicator =
      /(>>?|\btee\b|\bcp\b|\bmv\b|\btouch\b|\brm\b|\bmkdir\b|\bsed\b[^|;&]*-i|\bdd\b|\btruncate\b|\binstall\b|\brsync\b|\bgit\s+apply\b|\bpatch\b|(\bcurl\b|\bwget\b)[^|;&]*(-o\b|-O\b)|\bnew-item\b|\bset-content\b|\badd-content\b|\bout-file\b|\bcopy-item\b|\bmove-item\b|\bremove-item\b)/i.test(
        cmd
      );

    // A "migrations" path segment appears in the command text.
    const mentionsMigrationsPath = /(^|[\/\\'"` (])migrations([\/\\'"` )]|$)/i.test(cmd);

    if (makesMigration || (writeIndicator && mentionsMigrationsPath)) {
      blocked = true;
      reason = `Blocked: this Bash command appears to write into a "migrations" directory: ${cmd}`;
    }
  }

  if (blocked) {
    console.log(
      JSON.stringify({
        hookSpecificOutput: {
          hookEventName: "PreToolUse",
          permissionDecision: "deny",
          permissionDecisionReason: reason,
        },
      })
    );
  }

  process.exit(0);
});
