function getTime(artliste) {
	now = new Date();
	for(i=0;i<artliste.length;i++) {	
		later = new Date(artliste[i][3],artliste[i][2]-1,artliste[i][1],artliste[i][4],artliste[i][5],artliste[i][6]);
		if (later>now) {
			days = (later - now) / 1000 / 60 / 60 / 24;
			daysRound = Math.floor(days);
			hours = (later - now) / 1000 / 60 / 60 - (24 * daysRound);
			hoursRound = Math.floor(hours);
			minutes = (later - now) / 1000 /60 - (24 * 60 * daysRound) - (60 * hoursRound);
			minutesRound = Math.floor(minutes);
			seconds = (later - now) / 1000 - (24 * 60 * 60 * daysRound) - (60 * 60 * hoursRound) - (60 * minutesRound);
			secondsRound = Math.round(seconds);
		  erg = "" + daysRound +"d ";
			erg+= hoursRound+"h ";
			erg+= minutesRound+"m ";
			erg+= secondsRound+"s ";
			$("#count_"+i).html(erg);
		}
	}
}
