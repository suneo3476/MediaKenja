// forked from yamineko's "jsPDFを使って、canvasをPDFに出力" http://jsdo.it/yamineko/rX5b
$("#makePDF").click( function() {

	var filename = 'mediacard' //仮
	var uniqueUrlStr = "http://media.cs.inf.shizuoka.ac.jp"
	var categoryStr = "【"+"今とても"+"】"
	var titleStr = "七輪で焼いたサンマが食べたい"
	var dateStr = "2014-12-14"
	var authorStr = "藤森雅人"
	var dateAuthorStr = dateStr+"  "+authorStr
	var bodyStr = "MMORPGは、プレイヤーが主人公役となって冒険をする過程で仲間を増やし、ダンジョンを攻略し、最後にボスを倒すという従来のコンピュータロールプレイングゲームをオンライン上で同一ワールド（サーバー）に集まった複数のプレイヤーとともに進めていくゲームだ。仮想空間で話し、チームを組み、助け合う人々は現実に存在するプレイヤーで、それぞれ思い通りのキャラクターとして参加している。この仮想空間での自分であるキャラクターと現実の自分との間にはどのような関係があるのだろうか。\n多くの欧米プレイヤーを有する日本発の「FF XIV：新生エオルゼア」を例にとると、日本のプレイヤーは概して自分の性とは逆のキャラクターを選択し、欧米プレイヤーは自分と同じ性で、ゲーム内での戦闘中の役割を反映するようなキャラクターを好む。味方の盾となって攻撃を一身に引き受けるタンクなら、外見もいかめしい巨体の種族を選ぶといった具合だ 。ゲームプレイの鍵となる役割選択にはプレイヤーの好みや性格が反映するが、キャラクターの外見にも同じく性格が関係している。小人の種族を選ぶプレイヤーの中には、実際に小柄というより周囲を和ませたいという性格から愛嬌のあるキャラクターを選んでいる場合があるだろう。一般的に欧米のプレイヤーのキャラクターが現実の自分を起点としているのに対し、日本のプレイヤーは現実の自分とは切り離したキャラクター作成を行っていると言える。日欧米のキャラクター作成における違いの背景には、「キャラクター」という語に対する意識の違いがあるのではないか。欧米では「キャラクター」が自分を表象するものと考えられているのに対し、多くの日本人にとって「キャラクター」は自分が演じている役柄なのではないか。男装、女装を気軽に楽しむという現象もその表れなのかもしれない。"
	var bibItem = [];

	var canvas = new fabric.Canvas('c',{backgroundColor : "#ffffff"})
	//文字列を投げると、canvas上でセンタリングできるleftの値を返す
	function centerWidth(str, margin){
		if(margin==null)	margin = 0
	    var canvas = document.getElementById('c')
	    if(canvas.getContext){
	        var context = canvas.getContext('2d')
	        var strWidth = context.measureText(str)
	        var result = canvas.width/2 - strWidth.width/1.5 - margin;
	        return result
	    }
	}
	//右寄せ版
	function rightWidth(str, margin){
	    if(margin==null)    margin = 0
	    var canvas = document.getElementById('c')
	    if(canvas.getContext){
	        var context = canvas.getContext('2d')
	        var strWidth = context.measureText(str)
	        var result = canvas.width - strWidth.width*2 - margin;
	        return result
	    }
	}
	function makeText(obj){
	    if(obj.fontWeight==null)    obj.fontWeight = 'normal'
	    return new fabric.Text(obj.str,{
	        left:obj.left,
	        right: obj.right,
	        top:obj.top,
	        fontFamily: 'MS 明朝',
	        fontSize: obj.fontSize,
	        fontWeight: obj.fontWeight,
	        fontStyle: 'normal',
	        textAlign: obj.textAlign,
	        lineHeight: obj.lineHeight
	    })
	}
	// String.prototype.byteLength = function() {
	// 	var l = 0
	// 	var str = escape(this.concat())
	// 	var length = str.length
	// 	if(str){
	// 		for(var i=0; i<length; i++){
	// 			if(str.charAt(i)=='%' && str.charAt(i+1) =='u'){
	// 				i+=5;
	// 				l++;
	// 			}else if(str.charAt(i)=='%' && (str.charAt(i+1)=='2' || str.charAt(i+1) == '3' || str.charAt(i+1) == '5' || str.charAt(i+1) == '7')){
	// 				// *+-./@_以外の文字
	// 				i+=2;
	// 			}
	// 			l++;
	// 		}
	// 		return l;
	// 	}else{
	// 		return 0;
	// 	}
	// }
	function insertNewLine(str, num){
		var canvas = document.getElementById('c')
		if(canvas.getContext){
		    var context = canvas.getContext('2d')
			var result = "　";
			var p = 1
			var c = 0;
			for(var c=2; c<=str.length; c++){
//				console.log(p+":"+(c-p)+":"+str.substr(p,c-p))
				result += str.charAt(c-1);
				if(str.charAt(c-1)=="\n"){
					p = c;
					result += "　" //段落頭
				}else if(str.substr(p,c-p).length % num == 0){
					result += "\n"
					p = c
				}
			}
			return result;
		}
	}
	var categoryText = makeText({
	    str:categoryStr,
	    left:centerWidth(categoryStr),
	    top:70,
	    fontSize: 18,
	    fontWeight: 'bold',
	    textAlign: 'right',
	    lineHeight: 1
	})
	var titleText = makeText({
	    str:titleStr,
	    left:centerWidth(titleStr),
	    top:95,
	    fontSize: 16,
	    textAlign: 'right',
	    lineHeight: 1,
	})
	var headerBar = new fabric.Rect({
	    left: 50,
	    top: 115,
	    fill: '#622423',
	    width:540,
	    height: 8
	})
	var dateAuthorText = makeText({
	    str: dateAuthorStr,
	    left:rightWidth(dateAuthorStr),
	    top:130,
	    fontSize: 16,
	    textAlign: 'right',
	    lineHeight: 1,
	});
	bodyStr = insertNewLine(bodyStr,36)
	var bodyText = makeText({
	    str: bodyStr,
	    left: 55,
	    right: 590,
	    top:145,
	    fontSize: 16,
	    textAlign: 'left',
	    lineHeight: 1.50
	})

	canvas.add(categoryText, titleText);
	canvas.add(headerBar);
	canvas.add(dateAuthorText);
	canvas.add(bodyText);
	var qrcodeUrl = "http://chart.apis.google.com/chart?cht=qr&chs=100x100&choe=Shift_JIS&chl=" + uniqueUrlStr
	fabric.Image.fromURL(qrcodeUrl, function(img) {
		canvas.add(img);
		var image = canvas.toDataURL();    // firfoxならtoblobで直接blobにして保存できます。
		var mm2pt = 2.834645669291;
	    var pdf = new jsPDF('p','mm','A4');
	    var w = 1197 / mm2pt;
	    var card = document.getElementById("c");
	    var h = (w/card.width) * card.height;
	    console.log(w,h)
	    pdf.addImage(image, 'png', 0, 0, w/2,h/2);
	    pdf.save('Test.pdf');
	}, {crossOrigin: 'Anonymous'});

});