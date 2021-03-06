/*! Interface v1.0 | (c) 2015 Capile Tecnodesign <ti@tecnodz.com> */
if(!('Z' in window)) {
    if('tdz' in window) window.Z=window.tdz;
    else window.Z = {};
}
(function(Z)
{
    var _is=false, _cu='/', _i=0, _sel='.tdz-i[data-url]', _base, _load=0, _loading={}, _ids={}, _q=[], _last;
/*!startup*/
    function startup(I)
    {
        if('length' in I) {
            if(I.length==0) return;
            if(I.length==1) I=I[0];
            else {
                for(var i=0;i<I.length;i++) startup(I[i]);
                return;
            }
        }
        if(I.getAttribute('data-startup')) return;
        I.setAttribute('data-startup', '1');
        if('init' in Z) Z.init(I);

        var b = Z.parentNode(I, '.tdz-i-box');
        if(!_base) _base = Z.parentNode(I, '.tdz-i-box').getAttribute('base-url');

        // activate checkbox and radio buttons in lists
        var active=false, ui=(I.className.search(/\btdz-i\b/)>-1)?(I.getAttribute('data-url')):(null), L=I.querySelector('.tdz-i-list');
        if(L) {
            active = true;
            var l=L.querySelectorAll('input[type=checkbox][value],.tdz-i-list input[type=radio][value]'), i=l.length;
            while(i-- > 0) Z.bind(l[i], 'change', updateInterfaceDelayed);
        }

        // bind links to Interface actions
        l=I.querySelectorAll('a[href^="'+_base+'"],.tdz-i-a');
        i=l.length;
        while(i-- > 0) if(!l[i].getAttribute('target')) Z.bind(l[i], 'click', loadInterface);

        // bind forms
        l=I.querySelectorAll('form[method="get"][action^="'+_base+'"]');
        i=l.length;
        while(i-- > 0) Z.bind(l[i], 'submit', loadInterface);

        L=null;
        delete(L);

        // bind other actions
        var S=I.querySelectorAll('*[data-action-schema]');
        i=S.length;
        while(i-- > 0) {
            var M = S[i].querySelectorAll('*[data-action-scope]'),j=M.length, N, k=S[i].getAttribute('data-action-schema'),u=S[i].getAttribute('data-action-url'), bt, bu;
            while(j-- > 0) {
                bu=M[j].getAttribute('data-action-scope');
                M[j].removeAttribute('data-action-scope');
                if(M[j].nodeName.toLowerCase()=='button') {
                    M[j].setAttribute('data-url', u+'?scope='+bu+'&uri='+encodeURIComponent(I.getAttribute('data-url')));
                    M[j].className = ((M[j].className)?(M[j].className+' '):(''))+'tdz-i--'+k;
                    Z.bind(M[j], 'click', loadAction);
                    bt = M[j].form.parentNode.parentNode;
                } else {
                    bt= M[j];
                }
                N = document.createElement('a');
                N.setAttribute('href', u+'?scope='+bu+'&uri='+encodeURIComponent(I.getAttribute('data-url')));
                N.className = 'tdz-i-button tdz-i--'+k;
                Z.bind(N, 'click', loadAction);
                //Z.bind(M[j], 'dblclick', loadAction);
                bt.appendChild(N);
                delete(bt);
            }
        }
        /*
        i=S.length;
        while(i-- > 0) {
            var M = S[i].querySelectorAll('*[data-action-item]'),j=M.length, N, k=S[i].getAttribute('data-action-schema'),u=S[i].getAttribute('data-action-url');
            while(j-- > 0) {
                N = document.createElement('a');
                N.setAttribute('href', u+'?item='+M[j].getAttribute('data-action-item'));
                N.className = 'tdz-i-button tdz-i--'+k;
                Z.bind(N, 'click', loadAction);
                Z.bind(M[j], 'dblclick', loadAction);
                M[j].appendChild(N);
            }
        }
        */

        // only full interfaces go beyond this point
        if(!ui) {
            return false;
        }

        if(active) {
            updateInterfaceDelayed();
        } else {
            updateInterface();
        }

        if(_noH) {
            if(_cu==I.getAttribute('data-url')) {
                _is = true;
            }
        }
        activeInterface(I);
        l=document.querySelectorAll('.tdz-i-header .tdz-i-title');
        i=l.length;
        while(i-- > 0) {
            if(!l[i].getAttribute('data-i')) {
                l[i].setAttribute('data-i', 1);
                Z.bind(l[i], 'click', activeInterface);
                Z.bind(l[i], 'dblclick', loadInterface);
            }
        }
        l=null;

        if(_noH) {
            _load--;
            if(_load==0) _noH = false;
            else return;

            setHashLink();
        }

        l=document.querySelectorAll('.tdz-i-header .tdz-i-title.tdz-i-off');
        i=l.length;
        while(i-- > 0) {
            console.log('....127', l[i]);
            l[i].parentNode.removeChild(l[i]);
        }

        parseHash(); // sets _H
        if(!_last) {
            // first run, doesn't need to reload current page if in hash
            // reduce _H with currently loaded interface
            i=_H.length;
            var h;
            while(i-- > 0) {
                h=_H[i];
                if(h.substr(0,1)=='?') h=_base+h;
                else if(h.substr(0,1)!='/') h = _base+'/'+h;
                if(document.querySelector('.tdz-i[data-url="'+h+'"]')) {
                    _H.splice(i,1);
                }
            }
        }


        if(!_is && _H.length>0) {
            var h,hu,hq;
            _noH = true;
            for(i=0;i<_H.length;i++) {
                h=_H[i];
                if(h.substr(0,1)=='?') h=_base+h;
                else if(h.substr(0,1)!='/') h = _base+'/'+h;
                loadInterface(h, a);
                _cu = h.replace(/\?.*/, '');
            }
        } else {
            while(_q.length>0) {
                var a=_q.shift();
                var f=a.shift();
                f.apply(I, a);
            }
            setHashLink();
            _is = true;
        }
        _last = new Date().getTime();
    }

    var _H=[], _noH=false;
    function parseHash()
    {
        var h = window.location + '',p=h.indexOf('#!');
        if(p<0 || h.length<p+2) {
            _H = [];
            return false;
        }
        h=h.substr(p+2);
        _H = h.split(/\,/g);
        return _H;
    }

/*!setHash*/
    function setHash(h)
    {
        if(_noH) return;
        // remove h from _H
        if(h) {
            if(h.indexOf(',')>-1) h=h.replace(/,/g, '%2C');
            var i=_H.length, hu=h.replace(/\?.*/, '');
            while(i-- > 0) {
                var pu=_H[i].replace(/\?.*/, '');
                if(pu==hu) {
                    _H.splice(i,1);
                }
            }
        }
        if(h) _H.push(h);

        if(_H.length==1) {
            var I = document.querySelector('.tdz-i-active[data-url]'), ch, p;
            p=I.getAttribute('data-url');
            if(I && p==window.location.pathname) {
                /*
                ch = I.getAttribute('data-qs');
                if(ch) ch = p+'?'+ch;
                else */
                ch = p;
                if(ch.substr(0,_base.length+1)==_base+'/') ch=ch.substr(_base.length+1);
            }
            if(ch==_H[0]) _H=[];
            delete(I);
            delete(ch);
        }

        var s=(_H.length==0)?(''):('!'+_H.join(','));
        if(window.location.hash!=s) window.location.hash=s;
    }

/*!reHash*/
    function reHash()
    {
        var l=document.querySelectorAll('.tdz-i-header .tdz-i-title[data-url]'), i=0,a,h,I;
        _H=[];
        for(i=0;i<l.length;i++) {
            h=l[i].getAttribute('data-url');
            if(I=document.querySelector('.tdz-i-body .tdz-i[data-url="'+h+'"][data-qs]')) {
                h+='?'+I.getAttribute('data-qs');
            }
            if(h.substr(0,_base.length+1)==_base+'/') h=h.substr(_base.length+1);
            if(l[i].className.indexOf(/\btdz-i-title-active\b/)>-1)a=h;
            else _H.push(h);
        }
        if(a) _H.push(a);
        setHash(false);
    }

/*!setHashLink*/
    function setHashLink()
    {
        var i=_H.length, o, hr;
        while(i-- > 0) {
            var pu=_H[i].replace(/\?.*/, '');
            if(pu.substr(0,1)!='/') pu=_base+'/'+pu;

            o=document.querySelector('a.tdz-i-title[data-url="'+pu+'"]');
            if(o) {
                hr = o.getAttribute('href');
                if(hr!=_H[i]) o.setAttribute('href', (_H[i].substr(0,1)!='/')?(_base+'/'+_H[i]):(_H[i]));
            }
        }
    }

/*!unloadInterface*/
    function unloadInterface(I)
    {
        var u=I.getAttribute('data-url'), b=Z.parentNode(I, '.tdz-i-box'), T=b.querySelector('.tdz-i-header .tdz-i-title[data-url="'+u+'"]');
            console.log('unloadInterface 258');
        T.parentNode.removeChild(T);
        delete(T);
        var B = I.previousSibling;
        I.parentNode.removeChild(I);
        delete(I);
        if(!(I=b.querySelector('.tdz-i-active[data-url]'))) {
            if(!B) B=b.querySelector('.tdz-i[data-url]');
            activeInterface(B);
        }
        delete(B);
        delete(b);
        delete(I);
        reHash();
    }

/*!loadInterface*/
    function loadInterface(e)
    {
        //tdz.trace('loadInterface');
        var I, m=false, t, q, urls=[], l, i,u,data,h={'Tdz-Action':'Interface'};
        if(typeof(e)=='string') {
            urls.push(e);
        } else {
            Z.stopEvent(e);
            if(I = Z.parentNode(this, '.tdz-i')) {
            } else if (I=Z.parentNode(this, '.tdz-i-title[data-url]')) {
                I = document.querySelector('.tdz-i[data-url="'+I.getAttribute('data-url')+'"]');
                if(!I) return true;
            } else return true;
            if(this.className.search(/\btdz-i--close\b/)>-1) {
                //console.log('close Interface!!!', I);
                if(u=this.getAttribute('href')) activeInterface(u);
                unloadInterface(I);
                return false;
            }

            if(_noH) _noH = false;

            var valid=true;
            if(this.className.search(/\btdz-i-a-(many|one)\b/)>-1) {
                valid = false;
                if(this.className.search(/\btdz-i-a-many\b/)>-1) {
                    m=true;
                    if(I.matchesSelector('.tdz-i-list-many')) valid = true;
                }
                if(this.className.search(/\btdz-i-a-one\b/)>-1) {
                    if(I.matchesSelector('.tdz-i-list-one')) valid = true;
                }
                if(!valid) return false;
            }
            if(t=this.getAttribute('data-url')) {
                l=_ids[I.getAttribute('data-url')];
                if(q=this.getAttribute('data-qs')) {
                    t=t.replace(/\?.*/, '');
                    q='?'+q;
                } else q='';
                if(t.indexOf('{id}')>-1) {
                    i=(l.length && !m)?(1):(l.length);
                    while(i-- > 0) urls.push(t.replace('{id}', l[i])+q);
                } else {
                    if(l.length>0) {
                        q+=(q)?('&'):('?');
                        q+='_uid='+l.join(',');
                    }
                    urls.push(t+q);
                }
            } else if(t=this.getAttribute('action')) {
                if(this.getAttribute('method').toLowerCase()=='post') {
                    var enc=this.getAttribute('enctype');
                    if(enc=='multipart/form-data') {
                        // usually file uploads
                        if('FormData' in window) {
                            data = new FormData(this);
                        }
                        h['Content-Type']=false;
                    } else {
                        h['Content-Type'] = enc;
                    }
                    if(!data) data = Z.formData(this);
                } else if(this.className.search(/\btdz-auto\b/)<0) {
                    t = t.replace(/\?.*$/, '')+'?'+Z.formData(this);
                }
                urls.push(t);
            } else {
                urls.push(this.getAttribute('href'));
            }
        }
        i=urls.length;
        var o, H;
        while(i-- > 0) {
            var url = urls[i].replace(/(\/|\/?\?.+)$/, '');
            if(!document.querySelector('.tdz-i-title[data-url="'+url+'"]')) {
                if(!H) H = document.querySelector('.tdz-i-box .tdz-i-header');
                if(H) {
                    Z.element.call(H, {e:'a',a:{'class':'tdz-i-title tdz-i-off','data-url':url,href:urls[i]}});
                }
            }

            o=document.querySelector('.tdz-i[data-url="'+url+'"]');
            if(!o) {
                o=Z.element.call(document.querySelector('.tdz-i-body'), {e:'div',a:{'class':'tdz-i tdz-i-off','data-url':url}});
            }
            var t=new Date().getTime();
            if(!(url in _loading) || t-_loading[url]>1000) {
                _loading[url]=t;
                Z.blur(Z.parentNode(o, '.tdz-i-body'));
                //Z.trace('loadInterface: ajax request');
                Z.ajax((urls[i].search(/\?/)>-1)?(urls[i].replace(/\&+$/, '')+'&ajax='+t):(urls[i]+'?ajax='+t), data, setInterface, interfaceError, 'html', o, h);
            }
            _load++;
            o=null;
        }
        return false;
    }


/*!loadAction*/
    function loadAction(e)
    {
        if(typeof(e)=='object' && ('stopPropagation' in e)) {

            e.stopPropagation();
            e.preventDefault();

            var u,t;
            if(this.nodeName.toLowerCase()=='button') {
                t=this.form.parentNode.parentNode;
                u=this.getAttribute('data-url');
            } else if(this.getAttribute('data-action-item')) {
                t=this;
                u=this.children[this.children.length -1].getAttribute('href');
            } else {
                t=this.parentNode;
                u=this.getAttribute('href');
            }
            var a=new Date().getTime();
            u=(u.search(/\?/)>-1)?(u.replace(/\&+$/, '')+'&ajax='+a):(u+'?ajax='+a);
            //Z.trace('loadAction: ajax request');
            Z.blur(t);
            Z.ajax(u, null, loadAction, interfaceError, 'html', t, {'Tdz-Action':'Interface'});
        } else {
            //Z.trace('loadAction: ajax response start');
            var f = document.createElement('div');
            f.innerHTML = e;
            var I = f.querySelector('.tdz-i[data-url] .tdz-i-preview');
            if(!I) I = f.querySelector('.tdz-i[data-url] .tdz-i-container');
            if(!I) I = f.querySelector('.tdz-i[data-url]');
            // get tdz-i only
            var t=this.parentNode.insertBefore(document.createElement('div'), this);
            t.className='tdz-i-scope-block';
            console.log('...');
            this.parentNode.removeChild(this);
            var i=0;
            while(i<I.children.length) {
                t.appendChild(I.children[i]);
                i++;
            }
            startup(t);
            Z.focus(t);
            delete(t);
            /*
            this.innerHTML = '';
            var i=0;
            while(i<I.children.length) {
                this.appendChild(I.children[i]);
                i++;
            }
            //startup???
            //console.log('loadAction complete');
            startup(this);
            //Z.init(Z.parentNode(this, '.tdz-i[data-url]'));
            Z.focus(this);
            //startup(Z.parentNode(this, '.tdz-i[data-url]'));
            */
            //tdz.trace('loadAction: ajax response end');
        }

        return false;
    }

/*!activeInterface*/
    function activeInterface(I)
    {
        console.log('activeInterface(1) ', I);
        var u, H;
        if(!I || typeof(I)=='string' || !Z.isNode(I)) {
            if(typeof(I)=='string') {
                u = I;
                I = document.querySelector('.tdz-i[data-url="'+u+'"]');
            } else {
                if('stopPropagation' in I) {
                    I.stopPropagation();
                    I.preventDefault();
                }
                I=this;
            }
        }
        if(I) u=I.getAttribute('data-url');
        //console.log('activeInterface: '+u);
        if(u) H = document.querySelector('.tdz-i-title[data-url="'+u+'"]');
        if(I==H) I = document.querySelector('.tdz-i[data-url="'+u+'"]');
        if(!I && !u) {
            // get u from hash?
            return false;
        } else if(!I) {
            //console.log('-- should load interface?');
            loadInterface(u);
            return false;
        }
        if(I.className.search(/\btdz-i-active\b/)<0) I.className += ' tdz-i-active';
        if(H && H.className.search(/\btdz-i-off\b/)>-1) H.className = H.className.replace(/\s*\btdz-i-off\b/, '');
        if(H && H.className.search(/\btdz-i-title-active\b/)<0) H.className += ' tdz-i-title-active';
        var h=I.getAttribute('data-url'), qs = I.getAttribute('data-qs');
        if(_is) {
            if(h!=_base) {
                if(h.substr(0,_base.length)==_base){
                    h=h.substr(_base.length);
                    if(h.substr(0,1)=='/') h = h.substr(1);
                }
                if(qs) h+='?'+qs;
                //window.location.hash = '!'+h;
            } else if(qs) {
                h = '?'+qs;
            } else {
                h = '';
            }
            setHash(h);
        }
        var R = document.querySelectorAll('.tdz-i-title-active,.tdz-i-active'),i=R.length;
        while(i-- > 0) {
            if(R[i]==H || R[i]==I) continue;
            R[i].className = R[i].className.replace(/\btdz-i-(title-)?active\b\s*/g, '').trim();
        }
        updateInterface(I);
        return false;
    }

/*!setInterface*/
    function setInterface(c)
    {
        //tdz.trace('setInterface');
        if(c) {
            var f = document.createElement('div');
            f.innerHTML = c;

            var r = f.querySelectorAll('a[data-action]'), i=r.length, ra;
            while(i-- > 0) {
                ra = r[i].getAttribute('data-action');
                if(ra && (ra in _A)) {
                    _A[ra].call(this, r[i]);
                }
                if(r[i].parentNode) r[i].parentNode.removeChild(r[i]);
                delete(r[i]);
            }

            var H = document.querySelector('.tdz-i-box .tdz-i-header'),
                Hs = f.querySelectorAll('.tdz-i-header > .tdz-i-title'),
                h, c;
            i = Hs.length;
            while(i-- > 0) {
                h=H.querySelector('.tdz-i-title[data-url="'+Hs[i].getAttribute('data-url')+'"]');
                //tdz.bind(Hs[i], 'click', activeInterface);
                if(i>0) {
                    c = Hs[i].querySelector('*[data-action="close"]');
                    if(!c) {
                        c = document.createElement('span');
                        c.className = 'tdz-i-a tdz-i--close';
                        c.setAttribute('data-action', 'close');
                        Z.bind(c, 'click', loadInterface);
                        Hs[i].appendChild(c);
                    }
                    c = null;
                }
                /*
                if(h) H.replaceChild(Hs[i], h);
                else H.appendChild(Hs[i]);
                */
                h=null;
            }
            var I = f.querySelector('.tdz-i');
            if(!I) {
                if(Z.node(this)) {
                    Z.focus(Z.parentNode(this, '.tdz-i-body'));
                } else {
                    Z.focus(document.querySelector('.tdz-i-body.tdz-blur'));
                }
                return false;
            }

            var u=I.getAttribute('data-url');
            if(u in _loading) {
                delete(_loading[u]);
            }

            if(Z.node(this)) {
                if(this.getAttribute('data-url')!=u) {
                    var R;
                    while(R=this.parentNode.querySelector('.tdz-i[data-url="'+u+'"]')) {
                        R.parentNode.removeChild(R);
                    }
                }
                this.parentNode.replaceChild(I, this);
            }
            startup(I);
            Z.focus(Z.parentNode(I, '.tdz-i-body'));

        }
        return false;
    }

/*!actionUnload*/
    var _A = {
        unload:function(o) {
            console.log('_A.unload 568');
            var 
              ru = o.getAttribute('data-url'),
              rn = document.querySelector('.tdz-i-box .tdz-i-header .tdz-i-title[data-url="'+ru+'"]');
            if(rn) rn.parentNode.removeChild(rn);
            rn = document.querySelector('.tdz-i-box .tdz-i-body .tdz-i[data-url="'+ru+'"]');
            if(rn) rn.parentNode.removeChild(rn);
        },
        status:function(o) {
            var pid = o.getAttribute('data-status');
            if(!pid) return;
            _bkg[pid] = {u:o.getAttribute('data-url'),m:o.getAttribute('data-message')};
            msg(_bkg[pid].m);
            Z.delay(msg, 5000, 'msg');
            Z.delay(checkBkg, 2000, 'checkBkg');
        },
        error:function(o) {
            if(o.getAttribute('data-message')) {
                msg(o.getAttribute('data-message'), 'tdz-i-error');
                Z.delay(msg, 5000, 'msg');
            }
        },
        redirect:function(o) {
            if(o.getAttribute('data-message')) {
                msg(o.getAttribute('data-message'));
                Z.delay(msg, 5000, 'msg');
            }
            var u = o.getAttribute('data-url') || o.getAttribute('href');
            if(!u) return false;
            var t=o.getAttribute('data-target') || o.getAttribute('target');
            if(t) {
                window.open(u, t).focus();
            } else {
                window.location.href=u;
            }
        }
    }

    function msg(s, c)
    {
        var M=document.querySelector('.tdz-i.tdz-i-active .tdz-i-msg');
        if(!M) {
            var I = document.querySelector('.tdz-i-active .tdz-i-summary');
            if(!I) I = document.querySelector('.tdz-i-active .tdz-i-container');
            if(!I) I = document.querySelector('.tdz-i-active');
            if(!I) return;
            M=Z.element({e:'div',p:{className:'tdz-i-msg'}}, I.children[0]);
        }
        if(!c) c='';
        else c+=' ';
        c+='tdz-i-msg';
        if(s) {
            c+=' tdz-m-active';
        } else {
            s=null;
            c+=' tdz-m-inactive';
        }
        if(M.className!=c)M.className=c;
        M.textContent=s;
        //Z.element.call(M, {c:s});
    }

    var _bkg={};
    function checkBkg()
    {
        var n;
        for(n in _bkg) {
            Z.ajax(_bkg[n].u, null, setInterface, interfaceError, 'html', document.querySelector('.tdz-i.tdz-i-active'), {'Tdz-Action':'Interface', 'Tdz-Param':n});
            delete(_bkg[n]);
        }

    }


/*!interfaceError*/
    function interfaceError()
    {
        console.log('ERROR', arguments, this);
        msg(tdz.l.Error, 'tdz-i-error');
        Z.delay(msg, 5000, 'msg');
    }

    function updateInterfaceDelayed(e)
    {
        if(arguments.length>0) e.stopPropagation();
        if(Z.isNode(this) && 'checked' in this) Z.checkInput(this, null, false);
        Z.delay(updateInterface, 100);

    }

/*!updateInterface*/
    function updateInterface(I)
    {

        if(!Z.isNode(I)) I = document.querySelector('.tdz-i-active'+_sel);
        if(!I) return false;
        var id=I.getAttribute('data-url');

        // fix lists
        var L = I.querySelector('.tdz-i-list');
        if(L) {
            _ids[id] = [];
            var l=L.querySelectorAll('input[name="uid[]"][value]:checked'), i=l.length;
            while(i-- > 0) _ids[id].push(l[i].value);

            var l=L.querySelectorAll('tr:not(.on) input[value]:checked'), i=l.length;
            while(i-- > 0) Z.parentNode(l[i],'tr').className += ' on';
        } else {
            if(!(id in _ids)) _ids[id] = [];
            if(I.getAttribute('data-id')) _ids[id].push(I.getAttribute('data-id'));
        }

        i=_ids[id].length;
        var cn=I.className.replace(/\btdz-i-list-(one|many)\b\s*/g, '').trim();
        if(i==1) cn += ' tdz-i-list-one';
        else if(i>1) cn+= ' tdz-i-list-many';
        if(I.className!=cn)I.className=cn;


    }

    if('modules' in Z) {
        Z.modules.Interface=_sel;
        Z.initInterface = startup;
        Z.loadInterface = loadInterface;
    } else {
        startup(document.querySelectorAll('.tdz-i[data-url]'));
    }

})(Z);