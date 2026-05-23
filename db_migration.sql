-- ── Migration: Category & Difficulty support ────────────────────────────────
-- Run this if upgrading from old schema. Safe to run on a fresh install too.

USE quiz_game;

-- Add category and difficulty columns to gamesession if not already present
ALTER TABLE gamesession
    ADD COLUMN IF NOT EXISTS category  VARCHAR(60)                       DEFAULT 'General',
    ADD COLUMN IF NOT EXISTS difficulty ENUM('easy','medium','hard')      DEFAULT 'easy';

-- ── Additional sample questions per category & difficulty ────────────────────

-- HTML / CSS (easy)
INSERT INTO question (question_text, difficulty, category) VALUES
('Which HTML tag is used to create a hyperlink?',                           'easy',   'HTML & CSS'),
('Which property sets the background color in CSS?',                        'easy',   'HTML & CSS'),
('What does the <br> tag do in HTML?',                                      'easy',   'HTML & CSS'),
('Which CSS property controls the text size?',                              'easy',   'HTML & CSS'),
('What does CSS stand for?',                                                'easy',   'HTML & CSS'),
('Which HTML attribute specifies an alternate text for an image?',          'medium', 'HTML & CSS'),
('What is the CSS box model?',                                              'medium', 'HTML & CSS'),
('Which CSS selector targets elements with a specific class?',              'medium', 'HTML & CSS'),
('What is the difference between inline and block elements?',               'medium', 'HTML & CSS'),
('Which CSS property is used to create a grid layout?',                     'hard',   'HTML & CSS'),
('What is the CSS specificity order (highest to lowest)?',                  'hard',   'HTML & CSS');

-- JavaScript (easy/medium/hard)
INSERT INTO question (question_text, difficulty, category) VALUES
('Which keyword declares a variable in modern JavaScript?',                 'easy',   'JavaScript'),
('What does === check in JavaScript?',                                      'easy',   'JavaScript'),
('Which method adds an element to the end of an array?',                    'easy',   'JavaScript'),
('What is a callback function?',                                            'medium', 'JavaScript'),
('What does Promise.all() do?',                                             'medium', 'JavaScript'),
('What is the difference between null and undefined?',                      'medium', 'JavaScript'),
('What is event delegation?',                                               'hard',   'JavaScript'),
('What does the "this" keyword refer to in an arrow function?',             'hard',   'JavaScript');

-- Networking
INSERT INTO question (question_text, difficulty, category) VALUES
('Which protocol is used to send emails?',                                  'easy',   'Networking'),
('What is an IP address?',                                                  'easy',   'Networking'),
('What does HTTP stand for?',                                               'easy',   'Networking'),
('What is the difference between TCP and UDP?',                             'medium', 'Networking'),
('What is subnetting?',                                                     'medium', 'Networking'),
('What does a router do?',                                                  'easy',   'Networking'),
('What is a MAC address?',                                                  'medium', 'Networking'),
('What is the OSI model and how many layers does it have?',                 'hard',   'Networking'),
('What is HTTPS and how does SSL/TLS work?',                                'hard',   'Networking');

-- Algorithms
INSERT INTO question (question_text, difficulty, category) VALUES
('What is the time complexity of binary search?',                           'medium', 'Algorithms'),
('What data structure does a stack use?',                                   'easy',   'Algorithms'),
('What is a linked list?',                                                  'easy',   'Algorithms'),
('What is the difference between BFS and DFS?',                             'medium', 'Algorithms'),
('What is dynamic programming?',                                            'hard',   'Algorithms'),
('What is the worst-case complexity of QuickSort?',                         'hard',   'Algorithms'),
('What is a hash table?',                                                   'medium', 'Algorithms');

-- Choices for HTML & CSS questions (question IDs offset after existing 5)
-- We use variable-style inserts; adapt question_id numbers if your DB differs.
-- These match the order inserted above (IDs 6-16 assuming fresh DB)

-- Q6: <a>
INSERT INTO choice (question_id, choice_text, is_correct)
SELECT question_id, choice_text, is_correct FROM (
  SELECT q.question_id, v.choice_text, v.is_correct
  FROM question q
  JOIN (
    SELECT 'Which HTML tag is used to create a hyperlink?' AS qtxt, '<a>'          AS choice_text, 1 AS is_correct UNION ALL
    SELECT 'Which HTML tag is used to create a hyperlink?', '<link>',              0 UNION ALL
    SELECT 'Which HTML tag is used to create a hyperlink?', '<href>',              0 UNION ALL
    SELECT 'Which HTML tag is used to create a hyperlink?', '<url>',               0
  ) v ON q.question_text = v.qtxt
  WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id)
) x;

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct
FROM question q
JOIN (
  SELECT 'Which property sets the background color in CSS?' AS qtxt, 'background-color' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'Which property sets the background color in CSS?', 'color',                0 UNION ALL
  SELECT 'Which property sets the background color in CSS?', 'bg-color',             0 UNION ALL
  SELECT 'Which property sets the background color in CSS?', 'fill',                 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What does the <br> tag do in HTML?' AS qtxt, 'Inserts a line break' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What does the <br> tag do in HTML?', 'Bold text', 0 UNION ALL
  SELECT 'What does the <br> tag do in HTML?', 'Creates a border', 0 UNION ALL
  SELECT 'What does the <br> tag do in HTML?', 'Defines a button', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'Which CSS property controls the text size?' AS qtxt, 'font-size' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'Which CSS property controls the text size?', 'text-size', 0 UNION ALL
  SELECT 'Which CSS property controls the text size?', 'font-weight', 0 UNION ALL
  SELECT 'Which CSS property controls the text size?', 'letter-spacing', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What does CSS stand for?' AS qtxt, 'Cascading Style Sheets' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What does CSS stand for?', 'Computer Style Syntax', 0 UNION ALL
  SELECT 'What does CSS stand for?', 'Creative Style System', 0 UNION ALL
  SELECT 'What does CSS stand for?', 'Colorful Style Sheets', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'Which HTML attribute specifies an alternate text for an image?' AS qtxt, 'alt' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'Which HTML attribute specifies an alternate text for an image?', 'title', 0 UNION ALL
  SELECT 'Which HTML attribute specifies an alternate text for an image?', 'src', 0 UNION ALL
  SELECT 'Which HTML attribute specifies an alternate text for an image?', 'label', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What is the CSS box model?' AS qtxt, 'Content, padding, border, and margin around an element' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What is the CSS box model?', 'A CSS 3D transform property', 0 UNION ALL
  SELECT 'What is the CSS box model?', 'A grid layout system', 0 UNION ALL
  SELECT 'What is the CSS box model?', 'A method for animating elements', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'Which CSS selector targets elements with a specific class?' AS qtxt, '.classname' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'Which CSS selector targets elements with a specific class?', '#classname', 0 UNION ALL
  SELECT 'Which CSS selector targets elements with a specific class?', '*classname', 0 UNION ALL
  SELECT 'Which CSS selector targets elements with a specific class?', '@classname', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What is the difference between inline and block elements?' AS qtxt, 'Block elements start on a new line; inline do not' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What is the difference between inline and block elements?', 'Inline elements are larger', 0 UNION ALL
  SELECT 'What is the difference between inline and block elements?', 'Block elements cannot have children', 0 UNION ALL
  SELECT 'What is the difference between inline and block elements?', 'There is no difference', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'Which CSS property is used to create a grid layout?' AS qtxt, 'display: grid' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'Which CSS property is used to create a grid layout?', 'display: flex', 0 UNION ALL
  SELECT 'Which CSS property is used to create a grid layout?', 'layout: grid', 0 UNION ALL
  SELECT 'Which CSS property is used to create a grid layout?', 'position: grid', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What is the CSS specificity order (highest to lowest)?' AS qtxt, 'Inline > ID > Class > Element' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What is the CSS specificity order (highest to lowest)?', 'Element > Class > ID > Inline', 0 UNION ALL
  SELECT 'What is the CSS specificity order (highest to lowest)?', 'Class > ID > Inline > Element', 0 UNION ALL
  SELECT 'What is the CSS specificity order (highest to lowest)?', 'ID > Inline > Element > Class', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

-- JS choices
INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'Which keyword declares a variable in modern JavaScript?' AS qtxt, 'let / const' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'Which keyword declares a variable in modern JavaScript?', 'var', 0 UNION ALL
  SELECT 'Which keyword declares a variable in modern JavaScript?', 'dim', 0 UNION ALL
  SELECT 'Which keyword declares a variable in modern JavaScript?', 'define', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What does === check in JavaScript?' AS qtxt, 'Strict equality (value and type)' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What does === check in JavaScript?', 'Value only', 0 UNION ALL
  SELECT 'What does === check in JavaScript?', 'Type only', 0 UNION ALL
  SELECT 'What does === check in JavaScript?', 'Reference equality', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'Which method adds an element to the end of an array?' AS qtxt, 'push()' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'Which method adds an element to the end of an array?', 'pop()', 0 UNION ALL
  SELECT 'Which method adds an element to the end of an array?', 'shift()', 0 UNION ALL
  SELECT 'Which method adds an element to the end of an array?', 'unshift()', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What is a callback function?' AS qtxt, 'A function passed as an argument to another function' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What is a callback function?', 'A function that calls itself', 0 UNION ALL
  SELECT 'What is a callback function?', 'A function that returns another function', 0 UNION ALL
  SELECT 'What is a callback function?', 'A function that only runs once', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What does Promise.all() do?' AS qtxt, 'Runs multiple promises in parallel and waits for all' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What does Promise.all() do?', 'Runs promises one by one sequentially', 0 UNION ALL
  SELECT 'What does Promise.all() do?', 'Cancels all pending promises', 0 UNION ALL
  SELECT 'What does Promise.all() do?', 'Returns the first resolved promise', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What is the difference between null and undefined?' AS qtxt, 'null is explicitly set; undefined means not yet assigned' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What is the difference between null and undefined?', 'They are identical', 0 UNION ALL
  SELECT 'What is the difference between null and undefined?', 'undefined means the variable does not exist', 0 UNION ALL
  SELECT 'What is the difference between null and undefined?', 'null is a number type', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What is event delegation?' AS qtxt, 'Attaching a single listener to a parent to handle child events' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What is event delegation?', 'Removing events from child elements', 0 UNION ALL
  SELECT 'What is event delegation?', 'Firing custom events manually', 0 UNION ALL
  SELECT 'What is event delegation?', 'Blocking event propagation with stopPropagation', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What does the "this" keyword refer to in an arrow function?' AS qtxt, 'The enclosing lexical (outer) scope' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What does the "this" keyword refer to in an arrow function?', 'The arrow function itself', 0 UNION ALL
  SELECT 'What does the "this" keyword refer to in an arrow function?', 'The global window object always', 0 UNION ALL
  SELECT 'What does the "this" keyword refer to in an arrow function?', 'undefined always', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

-- Networking choices
INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'Which protocol is used to send emails?' AS qtxt, 'SMTP' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'Which protocol is used to send emails?', 'FTP', 0 UNION ALL
  SELECT 'Which protocol is used to send emails?', 'HTTP', 0 UNION ALL
  SELECT 'Which protocol is used to send emails?', 'SSH', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What is an IP address?' AS qtxt, 'A unique numerical label assigned to each device on a network' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What is an IP address?', 'A physical address burned into hardware', 0 UNION ALL
  SELECT 'What is an IP address?', 'The URL of a website', 0 UNION ALL
  SELECT 'What is an IP address?', 'A password for network access', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What does HTTP stand for?' AS qtxt, 'HyperText Transfer Protocol' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What does HTTP stand for?', 'HighText Transfer Protocol', 0 UNION ALL
  SELECT 'What does HTTP stand for?', 'HyperText Transport Process', 0 UNION ALL
  SELECT 'What does HTTP stand for?', 'HyperText Terminal Protocol', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What is the difference between TCP and UDP?' AS qtxt, 'TCP is reliable and ordered; UDP is fast but connectionless' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What is the difference between TCP and UDP?', 'UDP is slower but more reliable', 0 UNION ALL
  SELECT 'What is the difference between TCP and UDP?', 'TCP is used only for video streaming', 0 UNION ALL
  SELECT 'What is the difference between TCP and UDP?', 'They are identical', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What is subnetting?' AS qtxt, 'Dividing a network into smaller sub-networks' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What is subnetting?', 'Encrypting network traffic', 0 UNION ALL
  SELECT 'What is subnetting?', 'Connecting two different internet providers', 0 UNION ALL
  SELECT 'What is subnetting?', 'Boosting Wi-Fi signal strength', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What does a router do?' AS qtxt, 'Forwards data packets between different networks' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What does a router do?', 'Stores website files', 0 UNION ALL
  SELECT 'What does a router do?', 'Converts analog signals to digital', 0 UNION ALL
  SELECT 'What does a router do?', 'Acts as an antivirus', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What is a MAC address?' AS qtxt, 'A hardware identifier assigned to a network interface card' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What is a MAC address?', 'An IP address for Apple computers', 0 UNION ALL
  SELECT 'What is a MAC address?', 'A type of wireless protocol', 0 UNION ALL
  SELECT 'What is a MAC address?', 'A software firewall address', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What is the OSI model and how many layers does it have?' AS qtxt, 'A networking framework with 7 layers' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What is the OSI model and how many layers does it have?', 'A security model with 5 layers', 0 UNION ALL
  SELECT 'What is the OSI model and how many layers does it have?', 'A data storage model with 4 layers', 0 UNION ALL
  SELECT 'What is the OSI model and how many layers does it have?', 'A routing model with 3 layers', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What is HTTPS and how does SSL/TLS work?' AS qtxt, 'HTTP over encrypted SSL/TLS connection using certificates' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What is HTTPS and how does SSL/TLS work?', 'A faster version of HTTP with no encryption', 0 UNION ALL
  SELECT 'What is HTTPS and how does SSL/TLS work?', 'A protocol only used for file transfers', 0 UNION ALL
  SELECT 'What is HTTPS and how does SSL/TLS work?', 'An email security standard', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

-- Algorithms choices
INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What is the time complexity of binary search?' AS qtxt, 'O(log n)' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What is the time complexity of binary search?', 'O(n)', 0 UNION ALL
  SELECT 'What is the time complexity of binary search?', 'O(n²)', 0 UNION ALL
  SELECT 'What is the time complexity of binary search?', 'O(1)', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What data structure does a stack use?' AS qtxt, 'LIFO (Last In, First Out)' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What data structure does a stack use?', 'FIFO (First In, First Out)', 0 UNION ALL
  SELECT 'What data structure does a stack use?', 'Random access', 0 UNION ALL
  SELECT 'What data structure does a stack use?', 'Priority-based ordering', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What is a linked list?' AS qtxt, 'A sequence of nodes where each node points to the next' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What is a linked list?', 'An array with a fixed size', 0 UNION ALL
  SELECT 'What is a linked list?', 'A tree structure with two children per node', 0 UNION ALL
  SELECT 'What is a linked list?', 'A hash table implementation', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What is the difference between BFS and DFS?' AS qtxt, 'BFS explores level by level; DFS goes deep first' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What is the difference between BFS and DFS?', 'BFS uses a stack; DFS uses a queue', 0 UNION ALL
  SELECT 'What is the difference between BFS and DFS?', 'They produce identical results always', 0 UNION ALL
  SELECT 'What is the difference between BFS and DFS?', 'DFS cannot handle cyclic graphs', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What is dynamic programming?' AS qtxt, 'Breaking problems into subproblems and storing results to avoid re-computation' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What is dynamic programming?', 'A programming language paradigm', 0 UNION ALL
  SELECT 'What is dynamic programming?', 'A way to write code at runtime', 0 UNION ALL
  SELECT 'What is dynamic programming?', 'Only useful for sorting algorithms', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What is the worst-case complexity of QuickSort?' AS qtxt, 'O(n²)' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What is the worst-case complexity of QuickSort?', 'O(n log n)', 0 UNION ALL
  SELECT 'What is the worst-case complexity of QuickSort?', 'O(log n)', 0 UNION ALL
  SELECT 'What is the worst-case complexity of QuickSort?', 'O(n)', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);

INSERT INTO choice (question_id, choice_text, is_correct)
SELECT q.question_id, v.choice_text, v.is_correct FROM question q
JOIN (
  SELECT 'What is a hash table?' AS qtxt, 'A data structure that maps keys to values using a hash function' AS choice_text, 1 AS is_correct UNION ALL
  SELECT 'What is a hash table?', 'A sorted array of key-value pairs', 0 UNION ALL
  SELECT 'What is a hash table?', 'A type of binary tree', 0 UNION ALL
  SELECT 'What is a hash table?', 'A circular linked list', 0
) v ON q.question_text = v.qtxt
WHERE NOT EXISTS (SELECT 1 FROM choice c WHERE c.question_id = q.question_id);
