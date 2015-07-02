<?php

/**
 * Cron_Log
 *
 * A lot of my projects integrate heavily with external API's, where you can't always
 * ensure they'll finish compleatly running as API's often go down or throw unexpected
 * errors.
 *
 * This is not a substitute for error logging! It gives high level visability on when
 * tasks were last run, how long they took, if they finished or not, and how many rows
 * were processed.
 *
 * @author James McFall <james@mcfall.geek.nz>
 */
class Cron_Log {

    private $_ci;
    private $_logId;
    
    public function __construct() {
        $this->_ci = &get_instance();
    }
    

    /**
     * Mark the log as started by creating a row in the `cron_logs` table.
     *
     * Note it loads the log row ID into memory.
     *
     * @param <string> $calledBy - i.e. Integration_Customer/fullImport
     * @return <boolean>
     */
    public function startCronLog($calledBy) {

        $now = new DateTime("now", new DateTimeZone("Pacific/Auckland"));


        $result = $this->_ci->db->insert("cron_log", array(
            "script" => $calledBy,
            "message" => "started",
            "start_time" => $now->format("Y-m-d H:i:s")
        ));

        if ($result) {
            $this->_logId = $this->_ci->db->insert_id();
        }

        return $result;
    }

    
    /**
     * Update the log row as you go along. New rows processed count or something.
     *
     * @param <string> $message
     * @return <boolean>
     */
    public function updateCronLog($message) {

        $now = new DateTime("now", new DateTimeZone("Pacific/Auckland"));

        $this->_ci->db->where("id", $this->_logId);
        $this->_ci->db->limit(1);

        $result = $this->_ci->db->update("cron_log", array(
            "message" => $message,
            "exit_time" => $now->format("Y-m-d H:i:s")
        ));

        return $result;
    }

    
    /**
     * Close the cron entry and mark as completed.
     *
     * @return <boolean>
     */
    public function finishCronLog() {
        $now = new DateTime("now", new DateTimeZone("Pacific/Auckland"));

        $this->_ci->db->where("id", $this->_logId);
        $this->_ci->db->limit(1);

        $result = $this->_ci->db->update("cron_log", array(
            "exit_time" => $now->format("Y-m-d H:i:s"),
            "status" => "complete"
        ));

        return $result;
    }

    
    /**
     * Get the last time a script was ran.
     * Included as a static method as it's directly related to all of this
     * functionality, but not specifically part of the classes object oriented
     * usage.
     *
     * @param <string> $script
     * @return <boolean|DateTime>
     */
    public static function getLastRan($script) {

        $ci = &get_instance();
        $ci->db->select("*")
                ->from("cron_log")
                ->where("script", $script)
                ->where("status", "complete")
                ->order_by("start_time", "desc")
                ->limit(1);

        $result = $ci->db->get();

        # If we found the last time the script was ran, build a DateTime and return it.
        if ($result->num_rows()) {

            $lastRan = DateTime::createFromFormat("Y-m-d H:i:s", $result->row()->start_time, new DateTimeZone("Pacific/Auckland"));

            return $lastRan;
        }

        return false;
    }

    /**
     *
     */
    public function buildTable() {
        throw new Exception("Not yet implemented");
    }

}
