<?php

/**
 * Use this file to output reports required for the SQL Query Design test.
 * An example is provided below. You can use the `asTable` method to pass your query result to,
 * to output it as a styled HTML table.
 */

$database = 'nba2019';
require_once('vendor/autoload.php');
require_once('include/utils.php');

/*
 * Example Query
 * -------------
 * Retrieve all team codes & names
 */
echo '<h1>Example Query</h1>';
$teamSql = "SELECT * FROM team";
$teamResult = query($teamSql);
// dd($teamResult);
echo asTable($teamResult);

/*
 * Report 1
 * --------
 * Produce a query that reports on the best 3pt shooters in the database that are older than 30 years old. Only 
 * retrieve data for players who have shot 3-pointers at greater accuracy than 35%.
 * 
 * Retrieve
 *  - Player name
 *  - Full team name
 *  - Age
 *  - Player number
 *  - Position
 *  - 3-pointers made %
 *  - Number of 3-pointers made 
 *
 * Rank the data by the players with the best % accuracy first.
 */
echo '<h1>Report 1 - Best 3pt Shooters</h1>';
// write your query here
$threePointShooterSql = 
"SELECT r.name as player_name, t.name as full_team_name, age, `number`, pos, CONCAT(FORMAT(3pt/3pt_attempted * 100, 2), '%') as 3pt_percentage, 3pt as number_of_3pointers_made
FROM roster as r
LEFT JOIN team as t ON r.team_code = t.code
LEFT JOIN player_totals as pt ON r.id = pt.player_id
WHERE pt.age > 30 AND (3pt/3pt_attempted * 100 > 35)
ORDER BY 3pt_percentage DESC
";

$threePointShooterResult = query($threePointShooterSql);

echo asTable($threePointShooterResult);

/*
 * Report 2
 * --------
 * Produce a query that reports on the best 3pt shooting teams. Retrieve all teams in the database and list:
 *  - Team name
 *  - 3-pointer accuracy (as 2 decimal place percentage - e.g. 33.53%) for the team as a whole,
 *  - Total 3-pointers made by the team
 *  - # of contributing players - players that scored at least 1 x 3-pointer
 *  - of attempting player - players that attempted at least 1 x 3-point shot
 *  - total # of 3-point attempts made by players who failed to make a single 3-point shot.
 * 
 * You should be able to retrieve all data in a single query, without subqueries.
 * Put the most accurate 3pt teams first.
 */
echo '<h1>Report 2 - Best 3pt Shooting Teams</h1>';
// write your query here
$threePointShootingTeamSql = "SELECT
-- t.name as team_name, r.name as player_name,
-- CONCAT(SUM(FORMAT(3pt/3pt_attempted * 100, 2)), '%' ) as 3pt_percentage
-- FROM roster as r
-- LEFT JOIN team as t ON r.team_code = t.code
-- INNER JOIN player_totals as pt ON r.id = pt.player_id
-- GROUP BY team_name, player_name, 3pt_percentage
-- HAVING COUNT(r.id) > 1

t.name as team_name, CONCAT(FORMAT(SUM(3pt)/SUM(3pt_attempted) * 100, 2), '%') as team_3pt_accuracy, SUM(3pt) as total_3pts,
SUM(case when 3pt >= 1 then 1 else 0 end) as no_of_contributing_player, SUM(case when 3pt_attempted >= 1 then 1 else 0 end) as no_of_attempting_player, SUM(case when 3pt <= 1 then 1 else 0 end) as no_of_players_wtihout_3pt_shot_scored
FROM roster as r
LEFT JOIN team as t ON r.team_code = t.code
LEFT JOIN player_totals as pt ON r.id = pt.player_id
GROUP BY team_name
ORDER BY team_3pt_accuracy DESC
";
$threePointShootingTeamResult = query($threePointShootingTeamSql);

echo asTable($threePointShootingTeamResult);

?>