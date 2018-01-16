<?php
/**
 * Page contents
 *
 * PHP version 5.3
 *
 * @category  Model
 * @package   Estudio
 * @author    Guilherme Capilé, Tecnodesign <ti@tecnodz.com>
 * @copyright 2014 Tecnodesign
 * @link      https://tecnodz.com/
 */
class Tecnodesign_Estudio_Content extends Tecnodesign_Model
{
   /**
     * Configurable behavior
     * This is only available for customizing Estudio, please use the tdzContent class
     * within your lib folder (not TDZ_ROOT!) or .ini files
     */

    public static 
        $contentType = array(
            'html'=>array(
                'fields'=>array(
                    'html'=>array('label'=>'Content','type'=>'html','required'=>true),
                ),
            ),
            'md'=>array(
                'fields'=>array(
                    'txt'=>array('label'=>'Content','type'=>'textarea','required'=>true),
                ),
            ),
            'txt'=>array(
                'fields'=>array(
                    'txt'=>array('label'=>'Content','type'=>'textarea','required'=>true),
                ),
            ),
            'feed'=>array(
                'fields'=>array(
                    'entry'=>array('label'=>'Channel', 'type'=>'choice', 'model'=>'tdzEntry', 'required'=>true),
                    'master'=>array('label'=>'Layout','type'=>'choice','model'=>'tdzEntry', 'method'=>'getMasters', ),
                    'limit'=>array('label'=>'Entries to display', 'type'=>'choice', 'choices'=>array('All',1,2,3,4,5,6,7,8,9,10,25=>25,100=>100,1000=>1000)),
                    'hpp'=>array('Entries per page', 'type'=>'choices', 'choices'=>array('All', 5=>5, 10=>10, 25=>25, 100=>100),),
                    'options'=>array('label'=>'Options','type'=>'choice', 'class'=>'checkbox nostyle', 'options'=>array('expanded'=>true, 'multiple'=>true,), 'choices'=>array('related'=>'Only articles related to the page','linkhome'=>'Display link to channel', 'preview'=>'Enable preview', 'filter'=>'Enable filters')),
                ),
            ),
            'media'=>array(
                'fields'=>array(
                /*
                    title: Media
                    fields:
                      src: { label: Media source, type: text, required: true, class: media }
                      title: { label: Title, type: text }
                      alt: { label: Alternative text, type: text }
                      format: { label: Render as, type: choice, choices: { "": Guess format from file, image: Image, video: Embedded video, audio: Embedded audio, flash: Flash presentation, pdf: Embedded PDF, download: Content for download } }
                      id: { label: Identifier, type: text }
                      href: { label: Link to, type: text }
                */
                ),
            ),
            'php'=>array(
                'fields'=>array(
                    'script'=>array('label'=>'PHP Script', 'type'=>'text'),
                    'pi'=>array('label'=>'Processing instructions', 'type'=>'textarea'),
                ),
            ),
        ),
        $widgets = array(
        ),
        $multiviewContentType=array('widget','php'), // which entry types can be previewed
        $disableExtensions=array();                  // disable the preview of selected extensions

    /**
     * Tecnodesign_Model schema
     */
    //--tdz-schema-start--2014-12-27 18:32:23
    public static $schema = array (
      'database' => 'estudio',
      'tableName' => 'tdz_contents',
      'label' => '*Contents',
      'className' => 'tdzContent',
      'columns' => array (
        'id' => array ( 'type' => 'string', 'increment' => 'auto', 'null' => false, 'primary' => true, ),
        'entry' => array ( 'type' => 'int', 'null' => true, ),
        'slot' => array ( 'type' => 'string', 'size' => '50', 'null' => true, ),
        'content_type' => array ( 'type' => 'string', 'size' => '100', 'null' => true, ),
        'content' => array ( 'type' => 'string', 'size' => '', 'null' => true, ),
        'position' => array ( 'type' => 'int', 'null' => true, ),
        'published' => array ( 'type' => 'datetime', 'null' => true, ),
        'version' => array ( 'type' => 'int', 'null' => true, ),
        'created' => array ( 'type' => 'datetime', 'null' => false, ),
        'updated' => array ( 'type' => 'datetime', 'null' => false, ),
        'expired' => array ( 'type' => 'datetime', 'null' => true, ),
      ),
      'relations' => array (
        'ContentDisplay' => array ( 'local' => 'id', 'foreign' => 'content', 'type' => 'many', 'className' => 'Tecnodesign_Estudio_ContentDisplay', ),
        'Entry' => array ( 'local' => 'entry', 'foreign' => 'id', 'type' => 'one', 'className' => 'Tecnodesign_Estudio_Entry', ),
      ),
      'scope' => array (
      ),
      'order' => array (
        'slot' => 'asc',
        'position' => 'asc',
        'version' => 'desc',
      ),
      'events' => array (
        'before-insert' => array ( 'actAs', ),
        'before-update' => array ( 'actAs', ),
        'before-delete' => array ( 'actAs', ),
        'after-insert' => array ( 'actAs', ),
        'after-update' => array ( 'actAs', ),
        'after-delete' => array ( 'actAs', ),
        'active-records' => 'expired is null',
      ),
      'form' => array (
        'content_type' => array ( 'bind' => 'content_type', 'type' => 'select', 'choices' => 'Tecnodesign_Estudio::config(\'content_types\')', 'class' => 'estudio-field-content-type', ),
        'content' => array ( 'bind' => 'content', 'type' => 'hidden', 'class' => 'estudio-field-content', ),
      ),
      'actAs' => array (
        'before-insert' => array ( 'auto-increment' => array ( 'id', ), 'timestampable' => array ( 'created', 'updated', ), 'sortable' => array ( 'position', ), ),
        'before-update' => array ( 'auto-increment' => array ( 'version', ), 'timestampable' => array ( 'updated', ), 'sortable' => array ( 'position', ), ),
        'before-delete' => array ( 'auto-increment' => array ( 'version', ), 'timestampable' => array ( 'updated', ), 'soft-delete' => array ( 'expired', ), 'sortable' => array ( 'position', ), ),
        'after-insert' => array ( 'versionable' => array ( 'version', ), ),
        'after-update' => array ( 'versionable' => array ( 'version', ), ),
        'after-delete' => array ( 'versionable' => array ( 'version', ), ),
      ),
    );
    protected $id, $entry, $slot, $content_type, $content, $position, $published, $version=false, $created, $updated=false, $expired, $ContentDisplay, $Entry;
    //--tdz-schema-end--
    protected static $content_types=null;
    protected $subposition, $show_at, $hide_at;
    public $pageFile;
    
    public static function preview($c)
    {
        if(!($c instanceof self)) {
            $c = self::find($c);
        }
        if($c) {
            return $c->render(true);
        }
        return false;
    }

    public function getContent()
    {
        // should be valid json
        if(substr($this->content, 0,1)!='{') {
            $this->content = json_encode(Tecnodesign_Yaml::load($this->content),true);
        }
        return $this->content;
    }


    public function getContents()
    {
        if(substr($this->content, 0,1)=='{') {
            $r = json_decode($this->content, true);
        } else if(preg_match('#^(---[^\n]+\n)?[a-z0-9\- ]+\:#i', $this->content)) {
            $r = str_replace('\r\n', "\n", Tecnodesign_Yaml::load($this->content));
            //$r = Tecnodesign_Yaml::load($this->content);
        } else {
            $r = $this->content;
        }
        if(!is_array($r)) {
            $r = array($r);
        }
        return $r;
    }

    public function getForm($scope)
    {
        $cn = get_called_class();
        if(!isset($cn::$schema['e-studio-configured'])) {
            $cn::$schema['e-studio-configured']=true;
            $cfg = Tecnodesign_Estudio::config('content_types');
            $cn::$schema['scope']['e-studio']=array('content_type','content');
            foreach($cfg as $tn=>$d) {
                foreach($d['fields'] as $fn=>$fd) {
                    if(isset($fd['model'])) {
                        $fd['model']=str_replace(array('tdzEntries'), array('tdzEntry'), $fd['model']);
                        $fd['choices']=$fd['model'];
                        unset($fd['model']);
                        if(isset($fd['method'])) {
                            $fd['choices'].='::'.$fd['method'].'()';
                            unset($fd['method']);
                        }
                    }
                    if(isset($fd['options'])) {
                        $fd['attributes']=$fd['options'];
                        unset($fd['options']);
                    }
                    if(isset($fd['required'])) {
                        if(!isset($fd['attributes'])) $fd['attributes']=array();
                        $fd['attributes']['required']=$fd['required'];
                        unset($fd['required']);
                    }
                    $n='content-'.$tn.'-'.$fn;
                    $cn::$schema['form'][$n]=$fd;
                    if(!isset($cn::$schema['form'][$n]['class'])) $cn::$schema['form'][$n]['class']='estudio-field-disabled estudio-field-contents estudio-content-'.$tn;
                    else $cn::$schema['form'][$n]['class']='estudio-field-disabled estudio-field-contents estudio-content-'.$tn.' '.$cn::$schema['form'][$n]['class'];
                    $cn::$schema['scope']['e-studio'][]=$n;
                }
            }
            $cn::$schema['scope']['e-studio'][]='show_at';
            $cn::$schema['scope']['e-studio'][]='hide_at';
        }
        $cn::$schema['scope'][$scope]=$cn::$schema['scope']['e-studio'];
        return parent::getForm($scope);
    }

    /*
    public static function contentTypes()
    {
        return static::$contentType;
        if(is_null(self::$content_types)) {
            self::$content_types=Tecnodesign_Estudio::$app->estudio['content_types'];
            self::$widgets=Tecnodesign_Estudio::$app->estudio['widgets'];
            if(is_array(self::$widgets) && count(self::$widgets)>0) {
                $wg=array();
                foreach(self::$widgets as $wk=>$w) {
                    $wg[$wk]=$w['label'];
                    unset($wk, $w);
                }
                self::$content_types['widget']=array('title'=>'Widgets','fields'=>array('app'=>array('label'=>'Widget','type'=>'choice','required'=>true,'choices'=>$wg)));
            } else {
                self::$widgets = array();
            }
        }
        return self::$content_types;
    }
    */

    public function render($display=false)
    {
        /*
        if(!$this->hasPermission('preview')) {
            return false;
        }
        */
        $id = Tecnodesign_Estudio_Entry::$s++;
        $code = $this->getContents();
        $code['slot']=$this->slot;
        $type = $this->content_type;
        if(file_exists($tpl=Tecnodesign_Estudio::$app->tecnodesign['templates-dir'].'/tdz-contents-'.$type.'.php')) {
            if(!isset($code['txt']) && isset($code[0])) {
                $code['txt']=$code[0];
                unset($code[0]);
            }
            $s = "<div id=\"c{$id}\" data-estudio-c=\"{$this->id}\">"
                . tdz::exec(array('script'=>$tpl, 'variables'=>$code))
                . '</div>';
            return $s;
        }
        $ct = (isset(static::$contentType[$type]))?(static::$contentType[$type]):(array());
        $call = (isset($ct['class']) && class_exists($ct['class']))?(array($ct['class'])):(array($this));
        if(isset($ct['method']) && method_exists($call[0], $ct['method'])) {
            $call[1] = $ct['method'];
        } else {
            $call[1] = 'render'.ucfirst($type);
        }
        $r = call_user_func($call, $code, $this);
        unset($call[0], $call);
        if($display) {
            $result = '';
            if(is_array($r)) {
                if(isset($r['before'])) {
                    $result .= $r['before'];
                }
                if(isset($r['export'])) {
                    $result .= eval("return {$r['export']};");
                } else {
                    $result .= (isset($r['content']))?($r['content']):('');
                }
            } else {
                $result .= $r;
            }
            unset($r);
            if($this->slot=='meta') return $result;
            $result = "<div id=\"c{$id}\" data-estudio-c=\"{$this->id}\">{$result}</div>";
            return $result;
        }
        return $r;
    }

    public static function renderMedia($code=null, $e=null)
    {
        if(!isset($code['src'])||$code['src']=='') {
            return '';
        }
        if(!isset($code['format'])||$code['format']=='') {
            $code['format']=tdz::fileFormat($code['src']);
        }
        $s='';
        if(preg_match('/(image|pdf|flash|download|video|audio)/', strtolower($code['format']), $m)) {
            $f=$m[1];
        } else {
            $f='download';
        }
        if($f=='image') {
            $s = '<img src="'.tdz::xmlEscape($code['src']).'"';
            if(isset($code['alt']) && $code['alt']) {
                $s .= ' alt="'.tdz::xmlEscape($code['alt']).'"';
            }
            if(isset($code['title']) && $code['title']) {
                $s .= ' title="'.tdz::xmlEscape($code['title']).'"';
            }
            if(isset($code['id']) && $code['id']) {
                $s .= ' id="'.tdz::xmlEscape($code['id']).'"';
            }
            $s .= ' />';
            if(isset($code['href']) && $code['href']) {
                $s = '<a href="'.tdz::xmlEscape($code['href']).'">'.$s.'</a>';
            }
        } else if($f=='video') {
            $s = '<video src="'.tdz::xmlEscape($code['src']).'"';
            if(isset($code['alt']) && $code['alt']) {
                $s .= ' alt="'.tdz::xmlEscape($code['alt']).'"';
            }
            if(isset($code['title']) && $code['title']) {
                $s .= ' title="'.tdz::xmlEscape($code['title']).'"';
            }
            if(isset($code['id']) && $code['id']) {
                $s .= ' id="'.tdz::xmlEscape($code['id']).'"';
            }
            $s .= ' autobuffer="true" controls="true">alternate part';
            // alternate -- using flash?
            $s .= '</video>';
        } else if($f=='flashzzzz') {
            $s = '<div src="'.tdz::xmlEscape($code['src']).'"';
            if(isset($code['alt']) && $code['alt']) {
                $s .= ' alt="'.tdz::xmlEscape($code['alt']).'"';
            }
            if(isset($code['title']) && $code['title']) {
                $s .= ' title="'.tdz::xmlEscape($code['title']).'"';
            }
            if(isset($code['id']) && $code['id']) {
                $s .= ' id="'.tdz::xmlEscape($code['id']).'"';
            }
            $s .= ' autobuffer="true" controls="true">alternate part';
            // alternate -- using flash?
            $s .= '</video>';
        } else {
            $s = '<p';
            if(isset($code['id']) && $code['id']) {
                $s .= ' id="'.tdz::xmlEscape($code['id']).'"';
            }
            $s .= '><a href="'.tdz::xmlEscape($code['src']).'">';
            $s .= (isset($code['title']) && $code['title'])?(tdz::xmlEscape($code['title'])):(basename($code['src']));
            $s .= '</a></p>';
        }
        return $s;
    }


    public static function renderHtml($code=null, $e=null)
    {
        if(is_array($code)) {
            $code = isset($code['html'])?($code['html']):(array_shift($code));
        }
        return trim($code);
    }

    public static function renderText($code=null)
    {
        if(is_array($code)) {
            $code = isset($code['txt'])?($code['txt']):(array_shift($code));
        }
        return $code;
    }

    public static function renderJpg()
    {
        \tdz::debug(__METHOD__, $code);
    }

    public static function renderMd($code=null)
    {
        return tdz::markdown(static::renderText($code));
    }

    public static function renderPhp($code=null, $e=null)
    {
        if(!is_array($code)) {
            $code = array('pi'=>$code);
        } else if(isset($code[0])) {
            $code['pi']=$code[0];
            unset($code[0]);
        }
        if(isset($code['script'])) {
            if($code['script'] && file_exists($f=Tecnodesign_Estudio::$app->tecnodesign['apps-dir'].'/'.$code['script'])) {
                $code['script']=$f;
            } else {
                unset($code['script']);
            }
        }
        if(isset($code['pi'])) {
            $code['pi']=trim($code['pi']);
            if(substr($code['pi'], 0,5)=='<'.'?php') $code['pi'] = trim(substr($code['pi'], 5));
        }
        if(Tecnodesign_Estudio::$cacheTimeout===false) {
            return tdz::exec($code);
        }
        return array('export'=>'tdz::exec('.var_export($code,true).')');
    }

    public static function renderWidget($code=null, $e=null)
    {
        if(!is_array($code) || !isset($code['app']) || !isset(self::$widgets[$code['app']])) {
            return false;
        }
        $app=self::$widgets[$code['app']];
        $call = array();
        if(isset($app['model']) && class_exists($app['model'])) {
            $call[0] = $app['model'];
            if(isset($app['method']) && method_exists($call[0], $app['method'])) {
                $call[1] = $app['method'];
                if(!Tecnodesign_Estudio::$cacheTimeout || (isset($app['cache']) && $app['cache'])) {
                    return call_user_func($call, $e);
                } else if(is_string($call[0])) {
                    return array('export'=>$call[0].'::'.$call[1].'('.var_export($e, true).')');
                } else {
                    return array('export'=>'call_user_func('.var_export($call, true).', '.var_export($e, true).')');
                }
            }
        }
    }

    public static function renderFeed($code=null, $e=null)
    {
        if(!is_array($code)) {
            $code = array('entry'=>$code);
        }
        $o = array('variables'=>$code);
        /**
         * $code should contain:
         *
         *   entry  (mandatory) integer  The feed id
         *   master (optional) string   The template to use
         *
         * If the entry is not found, it should use current feed as a parameter
         */
        $o['script'] = Tecnodesign_Estudio::templateFile((isset($code['master']))?($code['master']):(null), 'tdz_feed');
        if(!is_numeric($o['variables']['entry'])) {
            $o['variables']['entry']=$e;
        }
        $E = ($e instanceof tdzEntry)?($e):(tdzEntry::find($o['variables']['entry']));
        if($E) {
            $o['variables'] += $E->asArray();
            $f = array('Relation.parent'=>$E->id);
            if(!(Tecnodesign_Estudio::$private && !Tecnodesign_Estudio::$cacheTimeout)) {
                $f['published<']=date('Y-m-d\TH:i:s');
            }
            $o['variables']['entries'] = tdzEntry::find($f,0,'preview',(isset($code['hpp']) && $code['hpp']),($E->type=='page')?(array('Relation.position'=>'asc','published'=>'desc')):(array('published'=>'desc')));
        }
        /*
        if(!Tecnodesign_Estudio::$cacheTimeout || (!isset($code['hpp']) || !$code['hpp'])) {
            return tdz::exec($o);
        }
        */
        unset($code);
        return array('export'=>'tdz::exec('.var_export($o,true).')');
    }
    
}