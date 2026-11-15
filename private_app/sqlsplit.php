<?php
/* =====================================================================
   Splitting a .sql file into separate statements.

   This cannot be done with a simple "split on every semicolon", because
   a semicolon can appear inside a piece of text, and a comment line can
   sit directly above a statement. So this walks the file one character
   at a time and keeps track of whether it is currently inside quotes.
   ===================================================================== */

/**
 * @return string[] the statements, in order, with comments removed
 */
function sql_statements(string $sql): array
{
    $out   = [];
    $buf   = '';
    $len   = strlen($sql);
    $quote = '';          // '' when outside a string, otherwise ' or "
    $i     = 0;

    while ($i < $len) {
        $c    = $sql[$i];
        $next = $i + 1 < $len ? $sql[$i + 1] : '';

        if ($quote !== '') {
            // inside a piece of text: copy everything through, and skip
            // the character after a backslash so \' does not end it
            $buf .= $c;
            if ($c === '\\' && $next !== '') { $buf .= $next; $i += 2; continue; }
            if ($c === $quote) { $quote = ''; }
            $i++;
            continue;
        }

        // a line comment: -- followed by a space, or #
        if (($c === '-' && $next === '-' && ($i + 2 >= $len || ctype_space($sql[$i + 2]) || $sql[$i + 2] === '-'))
            || $c === '#') {
            while ($i < $len && $sql[$i] !== "\n") $i++;
            continue;
        }

        // a block comment
        if ($c === '/' && $next === '*') {
            $end = strpos($sql, '*/', $i + 2);
            $i = $end === false ? $len : $end + 2;
            continue;
        }

        if ($c === "'" || $c === '"') { $quote = $c; $buf .= $c; $i++; continue; }

        if ($c === ';') {
            $stmt = trim($buf);
            if ($stmt !== '') $out[] = $stmt;
            $buf = '';
            $i++;
            continue;
        }

        $buf .= $c;
        $i++;
    }

    $stmt = trim($buf);
    if ($stmt !== '') $out[] = $stmt;

    return $out;
}
