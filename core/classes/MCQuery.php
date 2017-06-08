<?php
/*
 *	Made by Samerton
 *  https://github.com/NamelessMC/Nameless/
 *  NamelessMC version 2.0.0-pr2
 *
 *  License: MIT
 *
 *  Minecraft server query class
 */

class MCQuery {
    // Query a single server
    // Returns array containing query result
    // Params:  $ip - full server IP address with port (separated by :) to query
    //          $type - type of query to use (internal or external)
    //          $language - query language object
    public static function singleQuery($ip = null, $type = 'internal', $language){
        if($ip){
            try {
                if($type == 'internal'){
                    // Internal query
                    $query_ip = explode(':', $ip['ip']);

                    if(count($query_ip) == 2){
                        $ping = new MinecraftPing($query_ip[0], $query_ip[1], 1);

                        if($ip['pre'] == 1)
                            $query = $ping->QueryOldPre17();
                        else
                            $query = $ping->Query();

                        if(isset($query['players']))
                            $return = array(
                                'status_value' => 1,
                                'status' => $language->get('general', 'online'),
                                'player_count' => Output::getClean($query['players']['online']),
                                'player_count_max' => Output::getClean($query['players']['max']),
                                'x_players_online' => str_replace('{x}', Output::getClean($query['players']['online']), $language->get('general', 'currently_x_players_online'))
                            );
                        else
                            $return = array(
                                'status_value' => 0,
                                'status' => $language->get('general', 'offline'),
                                'server_offline' => $language->get('general', 'server_offline')
                            );

                        $ping->close();

                        return $return;
                    }

                } else {
                    // External query
                    ExternalMCQuery::addServer($ip['ip']);
                    $query = ExternalMCQuery::queryServers('basic');

                    if(isset($query->status))
                        $return = array(
                            'status_value' => 1,
                            'status' => $language->get('general', 'online'),
                            'player_count' => Output::getClean($query->players->online),
                            'player_count_max' => Output::getClean($query->players->max),
                            'x_players_online' => str_replace('{x}', Output::getClean($query->players->online), $language->get('general', 'currently_x_players_online'))
                        );
                    else
                        $return = array(
                            'status_value' => 0,
                            'status' => $language->get('general', 'offline'),
                            'server_offline' => $language->get('general', 'server_offline')
                        );

                    return $return;
                }
            } catch(Exception $e){
                return array(
                    'error' => true,
                    'value' => $e->getMessage()
                );
            }
        }
        return false;
    }

    // Query multiple servers
    // Returns array containing query result
    // Params:  $ip - full server IP address with port (separated by :) to query
    //          $type - type of query to use (internal or external)
    //          $language - query language object
    //          $accumulate - whether to return as one accumulated result or not
    public static function multiQuery($servers, $type = 'internal', $language, $accumulate = false){
        if(count($servers)){
            if($type == 'internal'){
                // Internal query
                $to_return = array();
                $total_count = 0;
                $status = 0;

                foreach($servers as $server){
                    $query_ip = explode(':', $server['ip']);

                    if(count($query_ip) <= 2){
                        try {
                            $ping = new MinecraftPing($query_ip[0], (isset($query_ip[1]) ? $query_ip[1] : 25565), 1);

                            if($server['pre'] == 1)
                                $query = $ping->QueryOldPre17();
                            else
                                $query = $ping->Query();
                        } catch(Exception $e){
                            // Unable to query
                            $query = array();
                        }

                        if(isset($query['players'])) {
                            if($accumulate === false){
                                $to_return[] = array(
                                    'name' => Output::getClean($server['name']),
                                    'status_value' => 1,
                                    'status' => $language->get('general', 'online'),
                                    'player_count' => Output::getClean($query['players']['online']),
                                    'player_count_max' => Output::getClean($query['players']['max']),
                                    'x_players_online' => str_replace('{x}', Output::getClean($query['players']['online']), $language->get('general', 'currently_x_players_online'))
                                );
                            } else {
                                if($status == 0)
                                    $status = 1;
                                $total_count += $query['players']['online'];
                            }
                        } else {
                            if($accumulate === true)
                                $to_return[] = array(
                                    'name' => Output::getClean($server['name']),
                                    'status_value' => 0,
                                    'status' => $language->get('general', 'offline'),
                                    'server_offline' => $language->get('general', 'server_offline')
                                );
                        }
                    }
                }
                $ping->close();

                if($accumulate === true)
                    $to_return = array(
                        'status_value' => $status,
                        'status' => (($status == 1) ? $language->get('general', 'online') : $language->get('general', 'offline')),
                        'status_full' => (($status == 1) ? str_replace('{x}', $total_count, $language->get('general', 'currently_x_players_online')) : $language->get('general', 'server_offline')),
                        'total_players' => $total_count
                    );

                return $to_return;
            } else {
                // External query
                foreach($servers as $server)
                    ExternalMCQuery::addServer($server['ip']);

                $query = ExternalMCQuery::queryServers('basic');

                if(count($query)){
                    $to_return = array();
                    $total_count = 0;
                    $status = 0;

                    foreach($query as $item){
                        if(isset($item->players)) {
                            if($accumulate === false){
                                $to_return[] = array(
                                    'name' => Output::getClean($server['name']),
                                    'status_value' => 1,
                                    'status' => $language->get('general', 'online'),
                                    'player_count' => Output::getClean($item->players->online),
                                    'player_count_max' => Output::getClean($item->players->max),
                                    'x_players_online' => str_replace('{x}', Output::getClean($item->players->online), $language->get('general', 'currently_x_players_online'))
                                );
                            } else {
                                if($status == 0)
                                    $status = 1;
                                $total_count += $item->players->online;
                            }
                        } else {
                            if($accumulate === true)
                                $to_return[] = array(
                                    'name' => Output::getClean($server['name']),
                                    'status_value' => 0,
                                    'status' => $language->get('general', 'offline'),
                                    'server_offline' => $language->get('general', 'server_offline')
                                );
                        }
                    }
                }

                if($accumulate === true)
                    $to_return = array(
                        'status_value' => $status,
                        'status' => (($status == 1) ? $language->get('general', 'online') : $language->get('general', 'offline')),
                        'status_full' => (($status == 1) ? str_replace('{x}', $total_count, $language->get('general', 'currently_x_players_online')) : $language->get('general', 'server_offline')),
                        'total_players' => $total_count
                    );

                return $to_return;
            }
        }
        return false;
    }
}