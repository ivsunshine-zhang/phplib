<?php

/**
 * Created by ivsunshine-zhang.
 * Date: 2015/8/24
 * Time: 13:45
 */
class MongodbInstance
{
    private static $instanceof = NULL;
    public $mongo;
    private $host = 'localhost';
    private $port = '27017';
    private $db;
    public $dbname;
    private $table = NULL;

    /**
     * ��ʼ���࣬�õ�mongo��ʵ������
     */
    public function __construct($host = NULL, $port = NULL, $dbname = NULL, $table = NULL)
    {
        if (NULL === $dbname) {
            $this->throwError('���ϲ���Ϊ�գ�');
        }

        // �ж��Ƿ񴫵���host��port
        if (NULL !== $host) {
            $this->host = $host;
        }

        if (NULL !== $port) {
            $this->port = $port;
        }

        $this->table = $table;

        $this->mongo = new MongoClient ($this->host . ':' . $this->port);
        if ($this->getVersion() >= '0.9.0') {
            $this->dbname = $this->mongo->selectDB($dbname);
            $this->db = $this->dbname->selectCollection($table);
        } else {
            $this->db = $this->mongo->$dbname->$table;
        }
    }

    public function getVersion()
    {
        return MongoClient::VERSION;
    }

    /**
     * ����ģʽ
     * @return Mongo|null
     */
    public static function getInstance($host = null, $port = null, $dbname = null, $table = null)
    {
        if (!(self::$instanceof instanceof self)) {
            self::$instanceof = new self ($host, $port, $dbname, $table);
        }
        return self::$instanceof;
    }

    /**
     * ����һ������
     * @param array $doc
     */
    public function insert($doc = array())
    {
        if (empty ($doc)) {
            $this->throwError('��������ݲ���Ϊ�գ�');
        }
        // ����������Ϣ
        try {
            if (!$this->db->insert($doc)) {
                throw new MongoException ('��������ʧ��');
            }
        } catch (MongoException $e) {
            $this->throwError($e->getMessage());
        }
    }

    /**
     * �������������Ϣ
     * @param array $doc
     */
    public function insertMulti($doc = array())
    {
        if (empty ($doc)) {
            $this->throwError('��������ݲ���Ϊ�գ�');
        }
        // ����������Ϣ
        foreach ($doc as $key => $val) {
            // �ж�$val�ǲ�������
            if (is_array($val)) {
                $this->insert($val);
            }
        }
    }

    /**
     * ����һ����¼
     * @return array|null
     */
    public function findOne($where = NULL)
    {
        if (NULL === $where) {
            try {
                if ($result = $this->db->findOne()) {
                    return $result;
                } else {
                    throw new MongoException ('��������ʧ��');
                }
            } catch (MongoException $e) {
                $this->throwError($e->getMessage());
            }
        } else {
            try {
                if ($result = $this->db->findOne($where)) {
                    return $result;
                } else {
                    throw new MongoException ('��������ʧ��');
                }
            } catch (MongoException $e) {
                $this->throwError($e->getMessage());
            }
        }
    }

    /**
     * todo �������������
     * �������е��ĵ�
     * @return MongoCursor
     */
    public function find($where = NULL)
    {
        if (NULL === $where) {

            try {
                if ($result = $this->db->find()) {
                } else {
                    throw new MongoException ('��������ʧ��');
                }
            } catch (MongoException $e) {
                $this->throwError($e->getMessage());
            }
        } else {
            try {
                if ($result = $this->db->find($where)) {
                } else {
                    throw new MongoException ('��������ʧ��');
                }
            } catch (MongoException $e) {
                $this->throwError($e->getMessage());
            }
        }

        $arr = array();
        foreach ($result as $id => $val) {
            $arr [] = $val;
        }

        return $arr;
    }

    /**
     * ��ȡ��¼����
     * @return int
     */
    public function getCount()
    {
        try {
            if ($count = $this->db->count()) {
                return $count;
            } else {
                throw new MongoException ('��������ʧ��');
            }
        } catch (MongoException $e) {
            $this->throwError($e->getMessage());
        }
    }

    /**
     * ��ȡ���е����ݿ�
     * @return array
     */
    public function getDbs()
    {
        return $this->mongo->listDBs();
    }

    /**
     * ɾ�����ݿ�
     * @param null $dbname
     * @return mixed
     */
    public function dropDb($dbname = NULL)
    {
        if (NULL !== $dbname) {
            $retult = $this->mongo->dropDB($dbname);
            if ($retult ['ok']) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
        $this->throwError('������Ҫɾ�������ݿ�����');
    }

    /**
     * ǿ�ƹر����ݿ������
     */
    public function closeDb()
    {
        $this->mongo->close(TRUE);
    }

    /**
     * ���������Ϣ
     * @param $errorInfo ��������
     */
    public function throwError($errorInfo = '')
    {
        echo "<h3>�����ˣ�$errorInfo</h3>";
        die ();
    }
}

?>