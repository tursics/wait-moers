var days = ["Mo", "Di", "Mi", "Do", "Fr", "Sa", "So"];
var width = 1000;
var height = 500;
var min_r = 2;
var max_r = 30;
var distance = 50;

var svg = d3.select("#graph").append("svg").attr("width", width).attr("height", height);

function select( d )
{
    if( d3.select( "#control-" + d ).attr( "class" ) == "selected" ) {
        d3.select( "#control-" + d ).attr( "class", "" );
        d3.select( "#group-"   + d ).attr( "class", "data" );
    } else {
        d3.selectAll( "#control > li" ).attr( "class", "" );
        d3.selectAll( "g.selected"    ).attr( "class", "data" )
        d3.select( "#control-" + d    ).attr( "class", "selected" );
        d3.select( "#group-"   + d    ).attr( "class", "selected" );
    }
}

function getDateOfISOWeek(w, y) {
    var simple = new Date(y, 0, 1 + (w - 1) * 7);
    var dow = simple.getDay();
    var ISOweekStart = simple;
    if (dow <= 4)
        ISOweekStart.setDate(simple.getDate() - simple.getDay() + 1);
    else
        ISOweekStart.setDate(simple.getDate() + 8 - simple.getDay());
    return ISOweekStart;
}

d3.json("data.php",
    function(d) {
//        console.log(d[0]);

		if(( d.length > 1) && (typeof d[d.length-1].lastwait !== "undefined")) {
			lastwait = d[d.length-1].lastwait;
			lastnumber = d[d.length-1].lastnumber;
			d.pop();
			document.getElementById( "number").innerHTML = "Ticket " + lastnumber;
			document.getElementById( "minutes").innerHTML = "ca. " + lastwait + " Minuten";
		} else {
			document.getElementById( "number").innerHTML = 'Zur Zeit';
			document.getElementById( "minutes").innerHTML = 'geschlossen';
		}

        waits = _.map(d, function(d) {
            return d.wait;
        });
        wds = _.map(d, function(d) {
            return d.weekday;
        });
        hrs = _.map(d, function(d) {
            return d.hour;
        });

//       console.log(_.uniq(wds));

        rscale = d3.scale.sqrt().domain([_.min(waits), _.max(waits)]).range([min_r, max_r]);
        yscale = d3.scale.linear().domain([_.min(wds), _.max(wds)]).range([distance, height - distance]);
        xscale = d3.scale.linear().domain([_.min(hrs), _.max(hrs)]).range([distance, width - distance]);

        xaxis = d3.svg.axis().scale(xscale).orient("bottom").ticks(11).tickValues([8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18]);
        yaxis = d3.svg.axis().scale(yscale).orient("left").ticks(6).tickValues([0, 1, 2, 3, 4, 5]).tickFormat(function(d) { return days[d] });

        d = _.map(d, function(d) {
            d.r = rscale(d.wait);
            d.x = xscale(d.hour);
            d.y = yscale(d.weekday);
            return d;
        });

        d = _.values(_.reduce(d, function(x, y) {
            if (x[y.week]) {
                x[y.week].push(y);
            } else {
                x[y.week] = [y];
            }
            return x;
        },
        {
    }));

    weeks = _.map(d, function(d) {
        return d[0].week;
    });

    svg.append("g")
      .attr("class", "axis")
      .attr("transform", "translate(" + [0, height - 20] + ")")
      .call(xaxis)

    svg.append("g")
      .attr("class", "axis")
      .attr("transform", "translate(" + [30, 0] + ")")
      .call(yaxis)

    legend = svg.append("g")
      .attr("transform", "translate(" + [width - distance - 150, 15] + ")")

    legend.append("rect")
      .attr("class", "legend")
      .attr("x", 0)
      .attr("y", 0)
      .attr("width", 150)
      .attr("height", 150)

	legend.append("text")
      .attr("x", 10)
      .attr("y", 20)
      .attr("text-anchor", "left")
      .text("Durchschnittliche")

	legend.append("text")
      .attr("x", 10)
      .attr("y", 40)
      .attr("text-anchor", "left")
      .text("Wartezeiten")

    legend.append("rect")
      .attr("class", "legend")
      .attr("x", 10)
      .attr("y", 50)
      .attr("width", 130)
      .attr("height", 0.5)

	legend.append("circle")
      .attr("cx", 10 + max_r)
      .attr("cy", 60 + max_r)
      .attr("r", min_r)

    legend.append("circle")
      .attr("cx", 110)
      .attr("cy", 60 + max_r)
      .attr("r", max_r)

    legend.append("text")
      .attr("x", 10 + max_r)
      .attr("y", 80 + max_r * 2)
      .attr("text-anchor", "middle")
      .text(_.min(waits)+" min")

    legend.append("text")
      .attr("x", 110)
      .attr("y", 80 + max_r * 2)
      .attr("text-anchor", "middle")
      .text(_.max(waits)+" min")

    svg.selectAll("g.data")
      .data(d)
      .enter()
      .append("g")
      .attr("id", function(d) {
          return "group-" + d[0].week
      })
      .attr("class", "data")
      .on("click", function(d) {
          select(d[0].week)
      })

    svg.selectAll("g.data")
      .selectAll("circle")
      .data(function(d) { return d })
      .enter()
      .append("circle")
      .attr("cx", function(d) { return d.x })
      .attr("cy", function(d) { return d.y })
      .attr("r", function(d) { return d.r })
      .append("title")
      .text(function(d) {
		var dt = getDateOfISOWeek( d.week, d.year);
		dt.setDate( dt.getDate() + d.weekday);
		return ""
		+ d.wait + " Minuten Wartezeit am "
		+ dt.getDate() + '.' + (dt.getMonth() + 1) + '.' + dt.getFullYear()
		+ ' (' + d.week + '. Woche)'
      })

    d3.select("#control")
      .selectAll("li")
      .data(weeks)
      .enter()
      .append("li")
      .attr("id", function(d) { return "control-" + d })
      .text(function(d) { return "Woche " + d; })
      .on("click", select)


})
