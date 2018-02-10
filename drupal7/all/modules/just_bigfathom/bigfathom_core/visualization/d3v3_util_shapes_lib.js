/*
 * Define shapes for D3 visualizations library of shapes
 *  
 * Copyright (c) 2015-2018 Room4me.com Software LLC, a Maryland USA company (room4me.com)
 * 
 * All rights reserved.  Contact author for more information.
 * This is BETA software.  No warranty or fitness for use is implied at this time.
 */

// SPECIAL CREDIT: Initial shape and D3 brainstorming with Armon Font 2015

//http://stackoverflow.com/questions/13983864/how-to-make-a-d3-plugin

if (typeof bigfathom_util === 'undefined' || !bigfathom_util.hasOwnProperty("shapes")) 
{
    var msg = "ERROR: You MUST load the bigfathom_util.shapes object first!  Check your javascript include files.";
    console.log(msg);
    throw msg;
};
if(!bigfathom_util.shapes.hasOwnProperty("lib"))
{
    //Create the object property because it does not already exist
    bigfathom_util.shapes.lib = {version: "20170205.1"};
};
bigfathom_util.shapes.lib.loaded = true;

//see shape names from http://fiddle.jshell.net/994XM/9/

bigfathom_util.shapes.lib.keyprops = {
    proj: {item_radius: 15
            , label:{offset:{dy:"-.5em"}}
            , status_cd:{offset:{dx:40, dy:".5em"}}
            , connector: {
                 d: "M15,0 L20,-4 L20,4 Z M20,0 L26,0 M30,0 m-4,0 a 4,4 0 1,1 0,.01 Z"
                ,stroke_width: 1
                ,stroke: "black"
                ,fill: "cyan"
                ,r:4
                ,offset:{x:30,y:0}
                ,flagsymbol:{
                     status:{offset:{x:30, y:0}, font:{size:".6em",dy:".25em",text_anchor:"middle",stroke_width:1, stroke:"black",fill:"green"}}
                    ,proj:{letter:'P', offset:{x:15, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,goal:{letter:'G', offset:{x:20, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,task:{letter:'T', offset:{x:25, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,equip:{letter:'Q', offset:{x:30, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,extrc:{letter:'X', offset:{x:35, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,equjb:{letter:'q', offset:{x:40, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,xrcjb:{letter:'x', offset:{x:45, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                }
            }
    },
    goal: {item_radius: 15
            , label:{offset:{dy:"-.5em"}}
            , status_cd:{offset:{dx:40, dy:"0.5em"}}
            , connector: {
                 d: "M15,0 L20,-4 L20,4 Z M20,0 L26,0 M30,0 m-4,0 a 4,4 0 1,1 0,.01 Z"
                ,stroke_width: 1
                ,stroke: "black"
                ,fill: "green"
                ,r:4
                ,offset:{x:30,y:0}
                ,flagsymbol:{
                     status:{offset:{x:30, y:0}, font:{size:".6em",dy:".25em",text_anchor:"middle",stroke_width:1, stroke:"black",fill:"green"}}
                    ,proj:{letter:'P', offset:{x:15, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,goal:{letter:'G', offset:{x:20, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,task:{letter:'T', offset:{x:25, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,equip:{letter:'Q', offset:{x:30, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,extrc:{letter:'X', offset:{x:35, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,equjb:{letter:'q', offset:{x:40, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,xrcjb:{letter:'x', offset:{x:45, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                }
            }
    },
    task: {item_radius: 15
            , label:{offset:{dy:"-.5em"}}
            , status_cd:{offset:{dx:40, dy:".5em"}}
            , connector: {
                 d: "M15,0 L20,-4 L20,4 Z M20,0 L26,0 M30,0 m-4,0 a 4,4 0 1,1 0,.01 Z"
                ,stroke_width: 1
                ,stroke: "black"
                ,fill: "blue"
                ,r:4
                ,offset:{x:30, y:0}
                ,flagsymbol:{
                    status:{offset:{x:30, y:0}, font:{size:".6em",dy:".25em",text_anchor:"middle",stroke_width:1, stroke:"black",fill:"green"}}
                    ,proj:{letter:'P', offset:{x:15, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,goal:{letter:'G', offset:{x:20, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,task:{letter:'T',offset:{x:20, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,equip:{letter:'Q', offset:{x:30, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,extrc:{letter:'X', offset:{x:35, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,equjb:{letter:'q', offset:{x:40, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,xrcjb:{letter:'x', offset:{x:45, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                }
            }
    },
    equjb: {item_radius: 15
            , label:{offset:{dy:"-.5em"}}
            , status_cd:{offset:{dx:40, dy:".5em"}}
            , connector: {
                 d: "M15,0 L20,-4 L20,4 Z M20,0 L26,0 M30,0 m-4,0 a 4,4 0 1,1 0,.01 Z"
                ,stroke_width: 1
                ,stroke: "black"
                ,fill: "gray"
                ,r:4
                ,offset:{x:35, y:0}
                ,flagsymbol:{
                    status:{offset:{x:30, y:0}, font:{size:".6em",dy:".25em",text_anchor:"middle",stroke_width:1, stroke:"black",fill:"green"}}
                    ,proj:{letter:'P', offset:{x:15, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,goal:{letter:'G', offset:{x:20, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,task:{letter:'T',offset:{x:20, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,equip:{letter:'Q', offset:{x:30, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,extrc:{letter:'X', offset:{x:35, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,equjb:{letter:'q', offset:{x:40, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,xrcjb:{letter:'x', offset:{x:45, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                }
            }
    },
    xrcjb: {item_radius: 15
            , label:{offset:{dy:"-.5em"}}
            , status_cd:{offset:{dx:40, dy:".5em"}}
            , connector: {
                 d: "M15,0 L20,-4 L20,4 Z M20,0 L26,0 M30,0 m-4,0 a 4,4 0 1,1 0,.01 Z"
                ,stroke_width: 1
                ,stroke: "black"
                ,fill: "gray"
                ,r:4
                ,offset:{x:35, y:0}
                ,flagsymbol:{
                    status:{offset:{x:30, y:0}, font:{size:".6em",dy:".25em",text_anchor:"middle",stroke_width:1, stroke:"black",fill:"green"}}
                    ,proj:{letter:'P', offset:{x:15, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,goal:{letter:'G', offset:{x:20, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,task:{letter:'T',offset:{x:20, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,equip:{letter:'Q', offset:{x:30, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,extrc:{letter:'X', offset:{x:35, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,equjb:{letter:'q', offset:{x:40, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                    ,xrcjb:{letter:'x', offset:{x:45, y:15}, font:{size:".5em",stroke_width:1, stroke:"black",fill:"gray"}}
                }
            }
    },
    equip: {item_radius: 15
            , label:{offset:{dy:"-.5em"}}
            , status_cd:{offset:{dx:40, dy:".5em"}}
    },
    extrc: {item_radius: 15
            , label:{offset:{dy:"-.5em"}}
            , status_cd:{offset:{dx:40, dy:".5em"}}
    }
};

bigfathom_util.shapes.lib.handy = {
    heart: {type:"path"
        , attr:{fill:"red", d:"M25,60 C100,0 25,-10 25,15 C25,-10 -50,0 25,60 Z"}
        , enhanced:{
            css:{
                name:{}
            }
        }
    },
    person: {type: "path"
        , attr: {fill: "cyan", d: "M10,10 C10,0 16,0 15,10 L14,10 L20,10 L20,13 L14,13 L15,15 L18,23 L14,23 L12,13 L10,23 L6,23 L9,15 L9,13 L0,12 L0,10 Z"}},
    prole: {type: "path"
        , attr: {fill: "#ffcc00", d: "M0 22 L32 22 L64 22 L64 -25 L48 -5 L32 -25 L16 -5 L0 -25 Z"}},
    srole: {type: "path"
        , attr: {fill: "#ff9900", d: "M16 30 L32 32 L48 30 L64 32 L60 26 L58 20 L58 16 L54 6 L48 4 L40 2 L40 0 L32 0 L24 0 L24 2 L16 4 L10 6 L6 16 L6 20 L4 26 L0 32 Z"}},
    
    task: {type: "path"
            , attr: {fill: "silver"
                , rotate:{a:-10, x:0,y:0}
                , d: "M -9 -9 L -9 -9 L -8 -9 L -8 -10 L -3 -10 L -2 -8 L -3 16 L 3 16 L 2 -8 L 4 -11 L 8 -11 L 12 -9 L 8 -15 L -8 -15 L -8 -16 L -9 -16 L -9 -16 Z"
                , original_d: "M14 14 L14 14 L16 14 L16 12 L26 12 L28 16 L26 64 L38 64 L36 16 L40 10 L48 10 L56 14 L48 2 L16 2 L16 0 L14 0 L14 0 Z"}},

    equip: {type: "path"
            , attr: {fill: "black"
                    ,translate:{x:-10,y:-10}
                    ,d: "M 20 , 14.5 v -2.9 l -1.8 -0.3 c -0.1 -0.4 -0.3 -0.8 -0.6 -1.4 l 1.1 -1.5 l -2.1 -2.1 l -1.5 , 1.1 c -0.5 -0.3 -1 -0.5 -1.4 -0.6 L 13.5 , 5 h -2.9 l -0.3 , 1.8 C9.8 , 6.9 , 9.4 , 7.1 , 8.9 , 7.4 L 7.4 , 6.3 L 5.3 , 8.4 l 1 , 1.5 c -0.3 , 0.5 -0.4 , 0.9 -0.6 , 1.4 L 4 , 11.5 v 2.9 l 1.8 , 0.3 c 0.1 , 0.5 , 0.3 , 0.9 , 0.6 , 1.4 l -1 , 1.5 l 2.1 , 2.1 l 1.5 -1 c 0.4 , 0.2 , 0.9 , 0.4 , 1.4 , 0.6 l 0.3 , 1.8 h 3 l 0.3 -1.8 c 0.5 -0.1 , 0.9 -0.3 , 1.4 -0.6 l 1.5 , 1.1 l 2.1 -2.1 l -1.1 -1.5 c 0.3 -0.5 , 0.5 -1 , 0.6 -1.4 L 20 , 14.5 z M 12 , 16 c -1.7 , 0 -3 -1.3 -3 -3 s 1.3 -3 , 3 -3 s 3 , 1.3 , 3 , 3 S 13.7 , 16 , 12 , 16 z"
                        , original_d: "M20,14.5v-2.9l-1.8-0.3c-0.1-0.4-0.3-0.8-0.6-1.4l1.1-1.5l-2.1-2.1l-1.5,1.1c-0.5-0.3-1-0.5-1.4-0.6L13.5,5h-2.9l-0.3,1.8 C9.8,6.9,9.4,7.1,8.9,7.4L7.4,6.3L5.3,8.4l1,1.5c-0.3,0.5-0.4,0.9-0.6,1.4L4,11.5v2.9l1.8,0.3c0.1,0.5,0.3,0.9,0.6,1.4l-1,1.5 l2.1,2.1l1.5-1c0.4,0.2,0.9,0.4,1.4,0.6l0.3,1.8h3l0.3-1.8c0.5-0.1,0.9-0.3,1.4-0.6l1.5,1.1l2.1-2.1l-1.1-1.5c0.3-0.5,0.5-1,0.6-1.4 L20,14.5z M12,16c-1.7,0-3-1.3-3-3s1.3-3,3-3s3,1.3,3,3S13.7,16,12,16z"
                    }
            , background: {fill: "gray", stroke: "orange", stoke_width: 1, opacity:0.45
                    ,translate:{x:-12,y:-12}
                    , d: "M0,0 L30,0 L30,30 L0,30 Z"
                
                    }
            },
            
    extrc: {type: "path"
            , attr: {fill: "black"
                        , d: "M10,10 C10,0 16,0 15,10 L14,10 L20,10 L20,13 L14,13 L15,15 L18,23 L14,23 L12,13 L10,23 L6,23 L9,15 L9,13 L0,12 L0,10 Z"
                    }
            , background: {fill: "gray", stroke: "orange", stoke_width: 1, opacity:0.45
                        , d: "M0,0 L30,0 L30,30 L0,30 Z"
                
                    }
            },    

    equjb: {type: "path"
            , attr: {fill: "black"
                        ,translate:{x:-10,y:-10}
                        ,d: "M 20 , 14.5 v -2.9 l -1.8 -0.3 c -0.1 -0.4 -0.3 -0.8 -0.6 -1.4 l 1.1 -1.5 l -2.1 -2.1 l -1.5 , 1.1 c -0.5 -0.3 -1 -0.5 -1.4 -0.6 L 13.5 , 5 h -2.9 l -0.3 , 1.8 C9.8 , 6.9 , 9.4 , 7.1 , 8.9 , 7.4 L 7.4 , 6.3 L 5.3 , 8.4 l 1 , 1.5 c -0.3 , 0.5 -0.4 , 0.9 -0.6 , 1.4 L 4 , 11.5 v 2.9 l 1.8 , 0.3 c 0.1 , 0.5 , 0.3 , 0.9 , 0.6 , 1.4 l -1 , 1.5 l 2.1 , 2.1 l 1.5 -1 c 0.4 , 0.2 , 0.9 , 0.4 , 1.4 , 0.6 l 0.3 , 1.8 h 3 l 0.3 -1.8 c 0.5 -0.1 , 0.9 -0.3 , 1.4 -0.6 l 1.5 , 1.1 l 2.1 -2.1 l -1.1 -1.5 c 0.3 -0.5 , 0.5 -1 , 0.6 -1.4 L 20 , 14.5 z M 12 , 16 c -1.7 , 0 -3 -1.3 -3 -3 s 1.3 -3 , 3 -3 s 3 , 1.3 , 3 , 3 S 13.7 , 16 , 12 , 16 z"
                    }
            , background: {fill: "gray", stroke: "silver", stoke_width: 1, opacity:0.45
                        ,translate:{x:-12,y:-12}
                        , d: "M0,0 L30,0 L30,30 L0,30 Z"
                    }
            },
    xrcjb: {type: "path"
            , attr: {fill: "black"
                        ,translate:{x:-10,y:-10}
                        , d: "M10,10 C10,0 16,0 15,10 L14,10 L20,10 L20,13 L14,13 L15,15 L18,23 L14,23 L12,13 L10,23 L6,23 L9,15 L9,13 L0,12 L0,10 Z"
                    }
            , background: {fill: "gray", stroke: "silver", stoke_width: 1, opacity:0.45
                        ,translate:{x:-12,y:-12}
                        , d: "M0,0 L30,0 L30,30 L0,30 Z"
                    }
            },    


    //goal: {type: "path", attr: {fill: "green", d: "M0 6 L20 18 L12 44 L32 24 L52 44 L44 18 L64 6 L38 6 L32 -20 L24 6 Z"}},
    
    
    group: {type: "path", attr: {fill: "cyan", d: "M0 6 L20 18 L12 44 L32 24 L52 44 L44 18 L64 6 L38 6 L32 -20 L24 6 Z"}},
    sprint: {type: "path", attr: {fill: "black", d: "M6 56 L32 40 L48 52 L54 40 L60 42 L54 34 L48 46 L36 34 L38 18 L46 18 L54 30 L56 28 L48 14 L36 12 L38 4 L32 0 L26 4 L30 12 L26 12 L20 20 L8 16 L10 16 L6 14 L4 16 L22 26 L28 18 L30 34 L8 52 L2 48 Z"}},
    brainstorm: {type: "path", attr: {fill: "blue", d: "M15 0 L5 20 L25 20 Z"}},

    trashcan: {type: "path", attr: {fill: "black", d: "M20 64 L44 64 L48 8 L50 4 L48 0 L16 0 L14 4 L16 8 Z"}},
    parkinglot: {type: "path", attr: {fill: "black", d: "M0 22 L16 18 L24 10 L48 10 L62 20 L62 32 L58 32 L58 36 L48 36 L48 32 L16 32 L16 36 L6 36 L6 32 L0 32 Z"}},
    
    prole_old: {type: "path", attr: {fill: "orange", d: "M0 64 L32 64 L64 64 L64 14 L48 32 L32 14 L16 32 L0 14 Z"}},
    prole_old2: {type: "path", attr: {fill: "#ffcc00", d: "M0 32 L32 32 L64 32 L64 0 L48 5 L32 0 L16 5 L0 0 Z"}},
    person_v2: {type: "path", attr: {fill: "cyan", d: "M10,10 C10,0 16,0 15,10 L14,10 L20,10 L20,13 L14,13 L15,15 L18,23 L14,23 L12,13 L10,23 L6,23 L9,15 L9,13 L0,12 L0,10 Z"}},
    goal_old2: {type: "path", attr: {fill: "black", d: "M20,50 A25,25,0 1 1 20,50 A25,25,0 1 1 0,50  Z"}}
    
};

bigfathom_util.shapes.lib.markers = {
    arrow2proj: {
        attr:{
            "id" : "arrow2proj", 
            "viewBox" : "0 -5 10 10", 
            "refX" : bigfathom_util.shapes.lib.keyprops.proj.item_radius, //30, 
            "refY" : 0, 
            "markerWidth" : 6, 
            "markerHeight" : 6, 
            "orient" : "auto"           
        },
        path:{
            "d" : "M0,-5L10,0L0,5"    
        }
    },
    arrow2goal: {
        attr:{
            "id" : "arrow2goal", 
            "viewBox" : "0 -5 10 10", 
            "refX" : bigfathom_util.shapes.lib.keyprops.goal.item_radius, //30, 
            "refY" : 0, 
            "markerWidth" : 6, 
            "markerHeight" : 6, 
            "orient" : "auto"           
        },
        path:{
            "d" : "M0,-5L10,0L0,5"    
        }
    },
    arrow2task: {
        attr:{
            "id" : "arrow2task", 
            "viewBox" : "0 -5 10 10", 
            "refX" : bigfathom_util.shapes.lib.keyprops.task.item_radius, //20, 
            "refY" : 0, 
            "markerWidth" : 6, 
            "markerHeight" : 6, 
            "orient" : "auto"            
        },
        path:{
            "d" : "M0,-5L10,0L0,5"    
        }
    },
    arrow2role: {
        attr:{
            "id" : "arrow2role", 
            "viewBox" : "0 -5 10 10", 
            "refX" : 20, 
            "refY" : 0, 
            "markerWidth" : 6, 
            "markerHeight" : 6, 
            "orient" : "auto"           
        },
        path:{
            "d" : "M0,-5L10,0L0,5"    
        }
    },
    arrow2sprint: {
        attr:{
            "id" : "arrow2sprint", 
            "viewBox" : "0 -5 10 10", 
            "refX" : 30, 
            "refY" : 0, 
            "markerWidth" : 6, 
            "markerHeight" : 6, 
            "orient" : "auto"          
        },
        path:{
            "d" : "M0,-5L10,0L0,5"    
        }
    },
    arrow2person: {
        attr:{
            "id" : "arrow2person", 
            "viewBox" : "0 -5 10 10", 
            "refX" : 35, 
            "refY" : 0, 
            "markerWidth" : 6, 
            "markerHeight" : 6, 
            "orient" : "auto"         
        },
        path:{
            "d" : "M0,-5L10,0L0,5"    
        }
    },
    arrow2group: {
        attr:{
            "id" : "arrow2group", 
            "viewBox" : "0 -5 10 10", 
            "refX" : 35, 
            "refY" : 0, 
            "markerWidth" : 6, 
            "markerHeight" : 6, 
            "orient" : "auto"            
        },
        path:{
            "d" : "M0,-5L10,0L0,5"    
        }
    },
    arrow: {
        attr:{
            "id" : "arrow", 
            "viewBox" : "0 -5 10 10", 
            "refX" : 30, 
            "refY" : 0, 
            "markerWidth" : 6, 
            "markerHeight" : 6, 
            "orient" : "auto"             
        },
        path:{
            "d" : "M0,-5L10,0L0,5"    
        }
    }
};
