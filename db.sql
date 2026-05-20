-- CEZ TechTrivia clean deployment database
-- Admin login: username admin, password Admin@123

SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS quiz_game;
CREATE DATABASE quiz_game;
USE quiz_game;

CREATE TABLE Player (
    player_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(45) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('player', 'admin') DEFAULT 'player',
    total_xp INT DEFAULT 0,
    level INT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE GameSession (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT,
    date_played DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_score INT DEFAULT 0,
    mode VARCHAR(45),
    xp_earned INT DEFAULT 0,
    category VARCHAR(60) DEFAULT 'General',
    difficulty ENUM('easy','medium','hard') DEFAULT 'easy',
    CONSTRAINT fk_gamesession_player
        FOREIGN KEY (player_id) REFERENCES Player(player_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Question (
    question_id INT AUTO_INCREMENT PRIMARY KEY,
    question_text VARCHAR(255) NOT NULL,
    difficulty ENUM('easy','medium','hard') DEFAULT 'easy',
    category VARCHAR(60) DEFAULT 'General',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_question_category_difficulty (category, difficulty)
) ENGINE=InnoDB;

CREATE TABLE Choice (
    choice_id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    choice_text VARCHAR(100) NOT NULL,
    is_correct TINYINT(1) DEFAULT 0,
    INDEX idx_choice_question (question_id),
    CONSTRAINT fk_choice_question
        FOREIGN KEY (question_id) REFERENCES Question(question_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE PlayerAnswer (
    answer_id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT,
    question_id INT,
    choice_id INT,
    is_correct TINYINT(1),
    CONSTRAINT fk_playeranswer_session
        FOREIGN KEY (session_id) REFERENCES GameSession(session_id) ON DELETE CASCADE,
    CONSTRAINT fk_playeranswer_question
        FOREIGN KEY (question_id) REFERENCES Question(question_id) ON DELETE SET NULL,
    CONSTRAINT fk_playeranswer_choice
        FOREIGN KEY (choice_id) REFERENCES Choice(choice_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE Achievement (
    achievement_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(45) NOT NULL,
    description VARCHAR(100) NOT NULL,
    condition_type VARCHAR(45) NOT NULL,
    condition_value INT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE PlayerAchievement (
    player_achievement_id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    achievement_id INT NOT NULL,
    date_unlocked DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_player_achievement (player_id, achievement_id),
    CONSTRAINT fk_playerachievement_player
        FOREIGN KEY (player_id) REFERENCES Player(player_id) ON DELETE CASCADE,
    CONSTRAINT fk_playerachievement_achievement
        FOREIGN KEY (achievement_id) REFERENCES Achievement(achievement_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `Rank` (
    rank_id INT AUTO_INCREMENT PRIMARY KEY,
    rank_name VARCHAR(45) NOT NULL UNIQUE,
    min_xp INT NOT NULL,
    max_xp INT NULL,
    medal VARCHAR(45) DEFAULT 'Bronze'
) ENGINE=InnoDB;

CREATE TABLE Streak (
    streak_id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT UNIQUE,
    current_streak INT DEFAULT 0,
    max_streak INT DEFAULT 0,
    last_played_date DATE,
    CONSTRAINT fk_streak_player
        FOREIGN KEY (player_id) REFERENCES Player(player_id) ON DELETE CASCADE
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO Player (username, email, password, role, total_xp, level) VALUES
('admin', 'admin@ceztechreviewer.com', '$2y$12$yr8X08uTiBNpRl1rJokoZewlJFWxRdIOUKtStNtRfLS/UYi9JIEAa', 'admin', 0, 1);

INSERT INTO `Rank` (rank_name, min_xp, max_xp, medal) VALUES
('Rookie', 0, 499, 'Bronze'),
('Debugger', 500, 1199, 'Bronze'),
('Code Runner', 1200, 2499, 'Silver'),
('Syntax Specialist', 2500, 4499, 'Silver'),
('Stack Solver', 4500, 6999, 'Gold'),
('Tech Master', 7000, 9999, 'Gold'),
('Legendary Architect', 10000, NULL, 'Platinum');

INSERT INTO Achievement (title, description, condition_type, condition_value) VALUES
('First Compile', 'Finish your first quiz.', 'quiz_count', 1),
('Warm Start', 'Finish 5 quizzes.', 'quiz_count', 5),
('Daily Driver', 'Finish 10 quizzes.', 'quiz_count', 10),
('Quiz Grinder', 'Finish 25 quizzes.', 'quiz_count', 25),
('Century Club', 'Reach 100 total XP.', 'total_xp', 100),
('XP Collector', 'Reach 500 total XP.', 'total_xp', 500),
('XP Hoarder', 'Reach 1000 total XP.', 'total_xp', 1000),
('Flawless Round', 'Score 10 in one quiz.', 'best_score', 10),
('Perfect Habit', 'Complete 3 perfect quizzes.', 'perfect_quiz_count', 3),
('Perfect Machine', 'Complete 10 perfect quizzes.', 'perfect_quiz_count', 10),
('Back Tomorrow', 'Build a 2 day streak.', 'current_streak', 2),
('Consistent Coder', 'Build a 5 day streak.', 'max_streak', 5),
('No Days Off', 'Build a 10 day streak.', 'max_streak', 10),
('Easy Mode Explorer', 'Complete 5 easy quizzes.', 'easy_quiz_count', 5),
('Easy Mode Veteran', 'Complete 20 easy quizzes.', 'easy_quiz_count', 20),
('Medium Climber', 'Complete 5 medium quizzes.', 'medium_quiz_count', 5),
('Medium Veteran', 'Complete 20 medium quizzes.', 'medium_quiz_count', 20),
('Hard Mode Brave', 'Complete 3 hard quizzes.', 'hard_quiz_count', 3),
('Hard Mode Veteran', 'Complete 15 hard quizzes.', 'hard_quiz_count', 15),
('Boss Level Brain', 'Complete 30 hard quizzes.', 'hard_quiz_count', 30);

CREATE TEMPORARY TABLE SeedQuestion (
    seed_id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(60) NOT NULL,
    difficulty ENUM('easy','medium','hard') NOT NULL,
    question_text VARCHAR(255) NOT NULL,
    correct_answer VARCHAR(100) NOT NULL
) ENGINE=Memory;

INSERT INTO SeedQuestion (category, difficulty, question_text, correct_answer) VALUES
('HTML & CSS','easy','What does HTML stand for?','HyperText Markup Language'),
('HTML & CSS','easy','Which tag creates a hyperlink in HTML?','<a>'),
('HTML & CSS','easy','Which HTML tag inserts a line break?','<br>'),
('HTML & CSS','easy','Which CSS property changes text color?','color'),
('HTML & CSS','easy','Which CSS property changes the background color?','background-color'),
('HTML & CSS','easy','Which attribute provides alternative text for an image?','alt'),
('HTML & CSS','easy','Which tag is used for the largest heading?','<h1>'),
('HTML & CSS','easy','Which CSS property controls text size?','font-size'),
('HTML & CSS','easy','Which HTML tag creates an ordered list?','<ol>'),
('HTML & CSS','easy','Which file extension is commonly used for CSS files?','.css'),
('HTML & CSS','medium','Which selector targets an element with id main?','#main'),
('HTML & CSS','medium','Which selector targets elements with class card?','.card'),
('HTML & CSS','medium','What are the four parts of the CSS box model?','Content, padding, border, margin'),
('HTML & CSS','medium','Which display value enables flexbox layout?','display: flex'),
('HTML & CSS','medium','Which display value enables CSS Grid layout?','display: grid'),
('HTML & CSS','medium','Which CSS property controls space between grid rows and columns?','gap'),
('HTML & CSS','medium','Which unit is relative to the root element font size?','rem'),
('HTML & CSS','medium','Which pseudo-class styles a link when hovered?','`:hover`'),
('HTML & CSS','medium','Which CSS property sets how an image fits inside its box?','object-fit'),
('HTML & CSS','medium','Which HTML element groups navigation links?','<nav>'),
('HTML & CSS','hard','Which CSS rule applies styles only under certain viewport conditions?','@media'),
('HTML & CSS','hard','Which CSS feature lets elements adapt styles based on container size?','Container queries'),
('HTML & CSS','hard','Which selector has higher specificity than a class selector?','ID selector'),
('HTML & CSS','hard','What does z-index control?','Stacking order'),
('HTML & CSS','hard','Which positioning value uses the nearest positioned ancestor?','absolute'),
('HTML & CSS','hard','Which CSS function chooses a value within min and max limits?','clamp()'),
('HTML & CSS','hard','Which attribute improves custom control accessibility by naming its purpose?','aria-label'),
('HTML & CSS','hard','Which CSS property can create smooth state changes?','transition'),
('HTML & CSS','hard','Which HTML loading value delays offscreen image loading?','lazy'),
('HTML & CSS','hard','Which CSS keyword allows a grid item to span every column?','1 / -1'),
('JavaScript','easy','Which keyword declares a block-scoped variable that can change?','let'),
('JavaScript','easy','Which keyword declares a block-scoped constant?','const'),
('JavaScript','easy','Which method adds an item to the end of an array?','push()'),
('JavaScript','easy','Which method removes the last item from an array?','pop()'),
('JavaScript','easy','What value does typeof null return?','object'),
('JavaScript','easy','Which operator checks strict equality?','==='),
('JavaScript','easy','Which browser method writes a message to the developer console?','console.log()'),
('JavaScript','easy','Which method selects the first matching CSS selector?','querySelector()'),
('JavaScript','easy','Which syntax creates a single-line comment?','// comment'),
('JavaScript','easy','Which JSON method converts an object to a string?','JSON.stringify()'),
('JavaScript','medium','What is a callback function?','A function passed to another function'),
('JavaScript','medium','What does Promise.all() wait for?','All promises to fulfill'),
('JavaScript','medium','Which array method creates a new array by transforming each item?','map()'),
('JavaScript','medium','Which array method keeps only items that pass a test?','filter()'),
('JavaScript','medium','Which keyword pauses inside an async function until a promise settles?','await'),
('JavaScript','medium','What is event delegation?','Handling child events from a parent listener'),
('JavaScript','medium','Which method attaches an event listener to an element?','addEventListener()'),
('JavaScript','medium','What does the spread syntax use?','...'),
('JavaScript','medium','Which statement handles errors from risky code?','try...catch'),
('JavaScript','medium','Which scope do arrow functions use for this?','Lexical this'),
('JavaScript','hard','What is a closure?','A function retaining access to outer scope'),
('JavaScript','hard','Which queue runs promise callbacks after the current call stack?','Microtask queue'),
('JavaScript','hard','What does debouncing limit?','How often a function runs after rapid events'),
('JavaScript','hard','What does throttling limit?','How often a function runs over time'),
('JavaScript','hard','Which API observes when an element enters the viewport?','IntersectionObserver'),
('JavaScript','hard','Which method cancels a scheduled requestAnimationFrame callback?','cancelAnimationFrame()'),
('JavaScript','hard','What is hoisting?','Declarations being processed before execution'),
('JavaScript','hard','Which object can cancel fetch requests?','AbortController'),
('JavaScript','hard','What does optional chaining prevent?','Errors from reading nullish properties'),
('JavaScript','hard','Which module syntax imports a named export?','import { name } from "module"'),
('Networking','easy','What does IP stand for?','Internet Protocol'),
('Networking','easy','What does DNS stand for?','Domain Name System'),
('Networking','easy','Which protocol is commonly used to browse websites?','HTTP'),
('Networking','easy','Which secure version of HTTP uses encryption?','HTTPS'),
('Networking','easy','Which command checks basic reachability to a host?','ping'),
('Networking','easy','Which device forwards traffic between networks?','Router'),
('Networking','easy','Which device connects devices inside a local network?','Switch'),
('Networking','easy','Which protocol is used to send email between mail servers?','SMTP'),
('Networking','easy','Which address is assigned to a network interface by hardware?','MAC address'),
('Networking','easy','Which protocol automatically assigns IP addresses?','DHCP'),
('Networking','medium','Which transport protocol provides reliable ordered delivery?','TCP'),
('Networking','medium','Which transport protocol is connectionless and low overhead?','UDP'),
('Networking','medium','Which port does HTTP use by default?','80'),
('Networking','medium','Which port does HTTPS use by default?','443'),
('Networking','medium','What does NAT do?','Translates private addresses to public addresses'),
('Networking','medium','What is subnetting?','Dividing a network into smaller networks'),
('Networking','medium','Which DNS record maps a name to an IPv4 address?','A record'),
('Networking','medium','Which DNS record maps a name to an IPv6 address?','AAAA record'),
('Networking','medium','Which protocol resolves IP addresses to MAC addresses on a LAN?','ARP'),
('Networking','medium','Which tool traces the route packets take to a destination?','traceroute'),
('Networking','hard','How many layers are in the OSI model?','7'),
('Networking','hard','Which OSI layer handles routing between networks?','Network layer'),
('Networking','hard','Which OSI layer handles TCP and UDP?','Transport layer'),
('Networking','hard','What does TLS primarily provide?','Encrypted and authenticated communication'),
('Networking','hard','Which firewall model tracks connection state?','Stateful firewall'),
('Networking','hard','What is CIDR notation used for?','Representing IP network prefixes'),
('Networking','hard','Which IPv4 private range starts with 10.?','10.0.0.0/8'),
('Networking','hard','What does a reverse proxy usually sit in front of?','Backend servers'),
('Networking','hard','Which DNS record identifies mail servers for a domain?','MX record'),
('Networking','hard','What problem does load balancing solve?','Distributing traffic across servers'),
('Algorithms','easy','Which data structure uses LIFO order?','Stack'),
('Algorithms','easy','Which data structure uses FIFO order?','Queue'),
('Algorithms','easy','Which search works by checking every item one by one?','Linear search'),
('Algorithms','easy','Which algorithm repeatedly swaps adjacent out-of-order items?','Bubble sort'),
('Algorithms','easy','Which data structure stores key-value pairs?','Hash table'),
('Algorithms','easy','Which structure has nodes connected by references?','Linked list'),
('Algorithms','easy','Which structure has a root and child nodes?','Tree'),
('Algorithms','easy','Which notation describes algorithm growth as input grows?','Big O'),
('Algorithms','easy','Which algorithm needs sorted input to split the search range?','Binary search'),
('Algorithms','easy','Which graph traversal uses a queue?','Breadth-first search'),
('Algorithms','medium','What is the time complexity of binary search?','O(log n)'),
('Algorithms','medium','What is the average time complexity of merge sort?','O(n log n)'),
('Algorithms','medium','Which graph traversal usually uses a stack or recursion?','Depth-first search'),
('Algorithms','medium','Which sorting algorithm picks a pivot and partitions items?','Quick sort'),
('Algorithms','medium','Which technique stores subproblem results to avoid recomputation?','Dynamic programming'),
('Algorithms','medium','Which structure supports fast min or max retrieval?','Heap'),
('Algorithms','medium','Which algorithm finds shortest paths with nonnegative weights?','Dijkstra algorithm'),
('Algorithms','medium','Which data structure is often used for prefix searching?','Trie'),
('Algorithms','medium','What is a collision in a hash table?','Two keys mapping to the same bucket'),
('Algorithms','medium','Which traversal visits left, root, then right in a binary tree?','In-order traversal'),
('Algorithms','hard','What is the worst-case time complexity of quick sort?','O(n^2)'),
('Algorithms','hard','Which algorithm finds a minimum spanning tree using edges by weight?','Kruskal algorithm'),
('Algorithms','hard','Which algorithm detects shortest paths with negative edges?','Bellman-Ford algorithm'),
('Algorithms','hard','Which technique explores possible solutions and undoes choices?','Backtracking'),
('Algorithms','hard','Which class contains problems verifiable in polynomial time?','NP'),
('Algorithms','hard','What is topological sorting used for?','Ordering directed acyclic graph dependencies'),
('Algorithms','hard','Which algorithm computes all-pairs shortest paths?','Floyd-Warshall algorithm'),
('Algorithms','hard','Which tree keeps itself balanced using rotations?','AVL tree'),
('Algorithms','hard','What is memoization?','Caching function results'),
('Algorithms','hard','Which technique proves greedy choices are safe?','Exchange argument'),
('Databases','easy','What does SQL stand for?','Structured Query Language'),
('Databases','easy','Which SQL command retrieves data?','SELECT'),
('Databases','easy','Which SQL command adds new rows?','INSERT'),
('Databases','easy','Which SQL command changes existing rows?','UPDATE'),
('Databases','easy','Which SQL command removes rows?','DELETE'),
('Databases','easy','Which key uniquely identifies a table row?','Primary key'),
('Databases','easy','Which key points to a row in another table?','Foreign key'),
('Databases','easy','Which clause filters rows?','WHERE'),
('Databases','easy','Which clause sorts query results?','ORDER BY'),
('Databases','easy','Which SQL command creates a table?','CREATE TABLE'),
('Databases','medium','Which join returns matching rows from both tables?','INNER JOIN'),
('Databases','medium','Which join returns all left table rows and matching right rows?','LEFT JOIN'),
('Databases','medium','Which aggregate counts rows?','COUNT()'),
('Databases','medium','Which clause filters grouped results?','HAVING'),
('Databases','medium','What does normalization reduce?','Data duplication and update anomalies'),
('Databases','medium','Which transaction property means all changes succeed or none do?','Atomicity'),
('Databases','medium','Which SQL feature speeds lookups on columns?','Index'),
('Databases','medium','Which constraint prevents duplicate values?','UNIQUE'),
('Databases','medium','Which command removes a table and its data?','DROP TABLE'),
('Databases','medium','Which SQL keyword limits duplicate result rows?','DISTINCT'),
('Databases','hard','What does ACID stand for in database transactions?','Atomicity, Consistency, Isolation, Durability'),
('Databases','hard','Which isolation problem reads uncommitted data?','Dirty read'),
('Databases','hard','Which isolation level prevents dirty reads but may allow non-repeatable reads?','Read committed'),
('Databases','hard','What is a deadlock?','Transactions waiting on each other forever'),
('Databases','hard','Which normal form removes transitive dependencies?','Third normal form'),
('Databases','hard','What does EXPLAIN show?','The query execution plan'),
('Databases','hard','Which index type is commonly used for equality lookups?','B-tree index'),
('Databases','hard','What is a composite index?','An index on multiple columns'),
('Databases','hard','What is a stored procedure?','Saved SQL logic executed by the database'),
('Databases','hard','Which replication setup has one writer and read replicas?','Primary-replica replication');

CREATE TEMPORARY TABLE SeedDistractor (
    category VARCHAR(60) NOT NULL,
    difficulty ENUM('easy','medium','hard') NOT NULL,
    slot TINYINT NOT NULL,
    choice_text VARCHAR(100) NOT NULL
) ENGINE=Memory;

INSERT INTO SeedDistractor (category, difficulty, slot, choice_text) VALUES
('HTML & CSS','easy',1,'Java compiler'),('HTML & CSS','easy',2,'Database engine'),('HTML & CSS','easy',3,'Network router'),
('HTML & CSS','medium',1,'Inline database query'),('HTML & CSS','medium',2,'Server firewall rule'),('HTML & CSS','medium',3,'Command-line package'),
('HTML & CSS','hard',1,'HTTP status code'),('HTML & CSS','hard',2,'SQL isolation level'),('HTML & CSS','hard',3,'Binary search step'),
('JavaScript','easy',1,'SELECT statement'),('JavaScript','easy',2,'CSS selector only'),('JavaScript','easy',3,'Network protocol'),
('JavaScript','medium',1,'HTML document type'),('JavaScript','medium',2,'Database primary key'),('JavaScript','medium',3,'IP subnet mask'),
('JavaScript','hard',1,'CSS grid track'),('JavaScript','hard',2,'SQL transaction lock'),('JavaScript','hard',3,'Router forwarding table'),
('Networking','easy',1,'JavaScript variable'),('Networking','easy',2,'CSS property'),('Networking','easy',3,'SQL aggregate'),
('Networking','medium',1,'HTML form tag'),('Networking','medium',2,'Array sorting method'),('Networking','medium',3,'Database index'),
('Networking','hard',1,'DOM event listener'),('Networking','hard',2,'CSS media rule'),('Networking','hard',3,'Primary key constraint'),
('Algorithms','easy',1,'HTML element'),('Algorithms','easy',2,'Email protocol'),('Algorithms','easy',3,'CSS property'),
('Algorithms','medium',1,'Database transaction'),('Algorithms','medium',2,'HTTP header'),('Algorithms','medium',3,'HTML attribute'),
('Algorithms','hard',1,'CSS pseudo-class'),('Algorithms','hard',2,'DNS record'),('Algorithms','hard',3,'SQL join type'),
('Databases','easy',1,'CSS declaration'),('Databases','easy',2,'JavaScript event'),('Databases','easy',3,'Network packet'),
('Databases','medium',1,'HTML heading'),('Databases','medium',2,'Graph traversal'),('Databases','medium',3,'TLS certificate'),
('Databases','hard',1,'CSS selector'),('Databases','hard',2,'Array method'),('Databases','hard',3,'Router protocol');

INSERT INTO Question (question_text, difficulty, category)
SELECT question_text, difficulty, category
FROM SeedQuestion
ORDER BY category, difficulty, seed_id;

CREATE TEMPORARY TABLE SeedChoice (
    seed_id INT NOT NULL,
    display_slot TINYINT NOT NULL,
    choice_text VARCHAR(100) NOT NULL,
    is_correct TINYINT(1) NOT NULL
) ENGINE=Memory;

INSERT INTO SeedChoice (seed_id, display_slot, choice_text, is_correct)
SELECT seed_id, (seed_id % 4) + 1, correct_answer, 1
FROM SeedQuestion;

INSERT INTO SeedChoice (seed_id, display_slot, choice_text, is_correct)
SELECT sq.seed_id,
       CASE
           WHEN sd.slot < ((sq.seed_id % 4) + 1) THEN sd.slot
           ELSE sd.slot + 1
       END AS display_slot,
       sd.choice_text,
       0
FROM SeedQuestion sq
JOIN SeedDistractor sd
  ON sd.category = sq.category
 AND sd.difficulty = sq.difficulty;

INSERT INTO Choice (question_id, choice_text, is_correct)
SELECT q.question_id, sc.choice_text, sc.is_correct
FROM SeedQuestion sq
JOIN Question q
  ON q.question_text = sq.question_text
 AND q.category = sq.category
 AND q.difficulty = sq.difficulty
JOIN SeedChoice sc
  ON sc.seed_id = sq.seed_id
ORDER BY q.question_id, sc.display_slot;

DROP TEMPORARY TABLE SeedChoice;
DROP TEMPORARY TABLE SeedDistractor;
DROP TEMPORARY TABLE SeedQuestion;
