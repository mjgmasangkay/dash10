<?php

class StatsService
{
	private array $searchArgs = [
		'player',
		'playerId',
		'team',
		'position',
		'country',
	];

	public function getStats($args, $type, $format)
	{
		$data = [];

		// validate search args
		$search = $args->filter(function($value, $key) {
			return in_array($key, $this->searchArgs);
		});

		switch ($type) {
			case 'playerstats':
			$data = $this->getPlayerStats($search);
			break;
			case 'players':
			$data = $this->getPlayers($search);
			break;
		}

		return $data;
	}

	public function getPlayerStats($search)
	{
		$where = $this->buildWhereClause($search);
		$sql = "
		SELECT roster.name, player_totals.*
		FROM player_totals
		INNER JOIN roster ON (roster.id = player_totals.player_id)
		WHERE $where";
		$data = query($sql) ?: [];

		// calculate totals
		return collect(query($sql))
		->map(function($item, $key) {
			unset($item['player_id']);
			$item['total_points'] = ($item['3pt'] * 3) + ($item['2pt'] * 2) + $item['free_throws'];
			$item['field_goals_pct'] = $item['field_goals_attempted'] ? (round($item['field_goals'] / $item['field_goals_attempted'], 2) * 100) . '%' : 0;
			$item['3pt_pct'] = $item['3pt_attempted'] ? (round($item['3pt'] / $item['3pt_attempted'], 2) * 100) . '%' : 0;
			$item['2pt_pct'] = $item['2pt_attempted'] ? (round($item['2pt'] / $item['2pt_attempted'], 2) * 100) . '%' : 0;
			$item['free_throws_pct'] = $item['free_throws_attempted'] ? (round($item['free_throws'] / $item['free_throws_attempted'], 2) * 100) . '%' : 0;
			$item['total_rebounds'] = $item['offensive_rebounds'] + $item['defensive_rebounds'];

			return $item;
		});
	}

	public function getPlayers($search)
	{
		$where = $this->buildWhereClause($search);
		$sql = "
		SELECT roster.*
		FROM roster
		WHERE $where";

		return collect(query($sql))
		->map(function($item, $key) {
			unset($item['id']);
			return $item;
		});
	}

	public function buildWhereClause($search)
	{
		$where = [];
		if ($search->has('playerId')) $where[] = "roster.id = '" . $search['playerId'] . "'";
		if ($search->has('player')) $where[] = "roster.name = '" . $search['player'] . "'";
		if ($search->has('team')) $where[] = "roster.team_code = '" . $search['team']. "'";
		if ($search->has('position')) $where[] = "roster.pos = '" . $search['position'] . "'";
		if ($search->has('country')) $where[] = "roster.nationality = '" . $search['country'] . "'";

		return implode(' AND ', $where);
	}


}