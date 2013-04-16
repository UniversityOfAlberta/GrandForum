library(car, lib.loc="libs/R/")
library(rjson, lib.loc="libs/R/")
library(mvtnorm, lib.loc="libs/R")
library(multcomp, lib.loc="libs/R/")
library(plotrix, lib.loc="libs/R/")
library(gtools, lib.loc="libs/R/")
library(R2HTML, lib.loc="libs/R/")

WIDTH <- 1024
HEIGHT <- 720

html <- HTMLStart(outdir="output", file="index", extension="html", echo=FALSE, HTMLframe=FALSE)
html <- HTMLInitFile("output", filename="index", extension="html", CSSFile="R2HTML.css", HTMLframe=FALSE, useLaTeX=FALSE, useGrid=FALSE)
html <- HTML("<meta charset='UTF-8'>")
html <- HTML.title("Network Statistics", HR=1)

config <- fromJSON(file="config.json")

setDev <- function(filename){
    if(dev.cur() != 1){
        dev.off()
    }
    png(filename=filename, width=WIDTH, height=HEIGHT)
}

# Parse Command-Line Arguments
args <- commandArgs(trailingOnly = TRUE)
Years <- args[1]:args[2]

all.is.numeric <- function(c){
    for(i in c){
        if(!is.numeric(i)){
            return(FALSE)
        }
    }
    return(TRUE)
}

all.is.finite <- function(c){
    for(i in c){
        if(!is.finite(i)){
            return(FALSE)
        }
    }
    return(TRUE)
}

all.is.identical <- function(c){
    prev <- NA
    for(i in c){
        if(!is.na(i) && !is.na(prev) && prev != i){
            return(FALSE)
        }
        if(!is.na(i) && !is.null(i)){
            prev <- i
        }
    }
    return(TRUE)
}

# Ops
applyOp <- function(c, op){
    if(is.null(op)){
        return(c)
    }
    if(op == "AVG"){
        return(AVG(c))
    }
    if(op == "MED"){
        return(MED(c))
    }
    if(op == "MAX"){
        return(MAX(c))
    }
    if(op == "MIN"){
        return(MIN(c))
    }
    if(op == "SUM"){
        return(SUM(c))
    }
    return(c)
}

MAX <- function(c){
    return(max(c))
}

MIN <- function(c){
    return(min(c))
}

AVG <- function(c){
    c <- na.omit(c)
    sum <- SUM(c)
    return(sum/length(c))
}

MED <- function(c){
    c <- order(na.omit(c))
    if(length(c) %% 2 == 0){
        return((c[(length(c)/2)-1]+c[(length(c)/2)])/2)
    }   
    return(c[length(c)/2])
}

SUM <- function(c){
    sum <- 0
    c <- na.omit(c)
    for(r in c){
        sum <- sum + r
    }
    return(sum)
}

summarizeNodes <- function(){
    nodes <- c("<ul>")
    for(node in config$nodes){
        nodes <- append(nodes, c("<li>", node, "</li>"))
    }
    nodes <- append(nodes, c("</ul>"))
    HTML(paste(nodes, sep='', collapse=''))
}

summarizeEdges <- function(){
    edges <- c("<ul>")
    for(edge in config$edges){
        edges <- append(edges, paste("<li>", edge$type, " (" , edge$source, " -> ", edge$target, ")", sep=''))
        edges <- append(edges, "<ul>")
        edges <- append(edges, paste("<li>", edge$desc, "</li>", sep=''))
        edges <- append(edges, "</ul>")
        edges <- append(edges, "</li>")
    }
    edges <- append(edges, "</ul>")
    HTML(paste(edges, sep='', collapse=''))
}

summarizeFields <- function(){
    fields <- c("<ul>")
    for(type in config$types){
        fields <- append(fields, c("<li>", type, "<ul>"))
        for(group in c("groups", "variables")){
            fields <- append(fields, c("<li>", group, "<ul>"))
            for(field in config$meta[[type]][[group]]){
                fields <- append(fields, c("<li>", field$name, " (", field$id, ")", "<ul>"))
                fields <- append(fields, c("<li>", field$desc, "</li>"))
                fields <- append(fields, "</ul>")
                fields <- append(fields, "</li>")
            }
            fields <- append(fields, "</ul>")
            fields <- append(fields, "</li>")
        }
        fields <- append(fields, "</ul>")
        fields <- append(fields, "</li>")
    }
    fields <- append(fields, "</ul>")
    HTML(paste(fields, sep='', collapse=''))
}

summarizeGraph <- function(){
    HTML.title("Nodes", HR=3)
    summarizeNodes()
    HTML.title("Edges", HR=3)
    summarizeEdges()
    HTML.title("Meta Fields", HR=3)
    summarizeFields()
}

correlate <- function(dataset){
    correlation <- cor(dataset[,fields], use="complete.obs", method="pearson")
    return(correlation)
}

renderCorChart <- function(field, prefix){
    filename <- paste("output/charts/cor/", prefix, "/", field,".png", sep='')
    setDev(filename)
    .mar <- par(mar=c(5.1,4.1,6.5,2.1))
    matplot(Years, Dataset4[,c(paste("Between.",field, sep=''), paste("Closeness.",field, sep=''), paste("PageRank.",field, sep=''))], type="b", lty=1, ylab=paste("Correlation of Centralities with ",field, sep=''), ylim=c(0,1), xaxt = "n")
    axis(1, at=args[1]:args[2], labels=args[1]:args[2])
    .xpd <- par(xpd=TRUE)
    legend(as.integer(args[1])-0.08, 1.20, legend=c(paste("Between.",field, sep=''),paste("Closeness.",field, sep=''), paste("PageRank.", field, sep='')), col=c(1,2,3), lty=1, pch=c("1","2","3"))
    par(mar=.mar)
    par(xpd=.xpd)

    HTML.title(paste("Correlation of", field, " with Centralities over Time", sep=' '), HR=3)
    HTMLInsertGraph(paste("../", filename, sep=''), WidthHTML=WIDTH, HeightHTML=HEIGHT, Align="left")
}

corStrength <- function(c){
    # http://faculty.quinnipiac.edu/libarts/polsci/Statistics.html
    est <- c$estimate
    strength <- ""
    direction <- ""
    
    if(is.na(est) || est == 0){
        direction <- ""
    }
    else if(est < 0){
        direction <- " negative"
    }
    else if(est > 0){
        direction <- " positive"
    }
    
    if(is.na(est)){
        strength <- "no"
        direction <- ""
    }
    else if(abs(est) >= 0.7){
        strength <- "a very strong"
    }
    else if(abs(est) >= 0.4 && abs(est) < 0.7){
        strength <- "a strong"
    }
    else if(abs(est) >= 0.3 && abs(est) < 0.4){
        strength <- "a moderate"
    }
    else if(abs(est) >= 0.2 && abs(est) < 0.3){
        strength <- "a weak"
    }
    else{
        strength <- "no or negligible"
        direction <- ""
    }
    return(paste(strength, direction, " relationship", sep=''))
}

anova <- function(field, field2){
    c <- list()
    d <- data.frame(field, field2)
    a <- aov(field2 ~ field, data=d)
    c$summary <- summary(a)
    pairs <- glht(a, linfct = mcp(field = "Tukey"))
    conf <- confint.default(pairs)
    c$df <- a$df.residual
    sorted <- sort(unique(d[rowSums(is.na(d)) == 0,"field"]))
    for(i in 1:length(conf)){
        if(i <= length(conf)/2){
            lower <- conf[i]
            upper <- conf[i + length(conf)/2]
            c$lower[i] <- lower
            c$upper[i] <- upper
            if(lower > 0 && upper > 0){
                c$change[i] <- ">"
            }
            else if(lower < 0 && upper < 0){
                c$change[i] <- "<"
            }
            else{
                c$change[i] <- "="
            }
        }
    }
    n <- 1
    i <- 1
    for(s1 in sorted){
        j <- 1
        for(s2 in sorted){
            if(j > i){
                c$g1[n] <- s2
                c$g2[n] <- s1
                n <- n + 1
            }
            j <- j + 1
        }
        i <- i + 1
    }
    return(c)
}

runCorTest <- function(field1, field2, f1, f2, year, prefix, chartType){
    filename <- paste("output/", year, "/charts/tests/", prefix, "/", f2, "_", f1, ".png", sep='')
    setDev(filename)
    outNames <- c()
    if(is.null(chartType) || chartType == "scatterplot"){
        scatterplot(field1, field2, reg.line=lm, smooth=TRUE, spread=TRUE, boxplots=FALSE, span=1, xlab=f1, ylab=f2)
    }
    else if(chartType == "boxplot" || chartType == "anova"){
        newField1 <- c()
        newField2 <- c()
        i <- 1
        for(f in field1){
            if(f != "" && !is.na(f)){
                newField1 <- c(newField1, f)
                newField2 <- c(newField2, field2[i])
            }
            i <- i + 1
        }
        field1 <- newField1
        field2 <- newField2
        labels <- sort(unique(field1))
        
        par(mar=c(12,4.1,6.5,2.1))
        p <- boxplot(field2~field1, xlab=f1, ylab=f2, axes = FALSE, axisnames = FALSE)
        
        staxlab(side=1,seq(1, length(labels), by=1),labels,nlines=1,top.line=0.5,line.spacing=0.8, srt = 45, cex=0.8)
        axis(1, labels = FALSE)
        axis(2)
        box("plot")
        outliers <- p$out
        
        group <- p$group
        groups <- sort(unique(field1))
        lastGr <- -1
        i <- 1
        names <- c()
        
        is.f1.time <- (f1 == "Time" || f1 == "Years")
        
        for(g in group){
            out <- outliers[i]
            gr <- groups[g]
            if(lastGr != gr){
                outNames <- c(outNames, paste("&nbsp;&nbsp;<b>for", f1, "=", gr, "</b>", sep=' '))
                lastGr <- gr
                if(is.f1.time){
                    names <- c()
                }
            }
            
            if(!is.f1.time){
                d <- Datasets[[year]]
                d <- d[d[[f1]] == gr & d[[f2]] == out & !is.na(d[[f2]]) & !is.null(d[[f2]]),]
            }
            else{
                d <- Datasets[[gr]]
                d <- d[d[[f2]] == out & !is.na(d[[f2]]) & !is.null(d[[f2]]),]
            }
            if(nrow(d) > 0){
                for(j in 1:nrow(d)){
                    v <- d[[f2]][j]
                    k <- 1
                    for(name in d$Name){
                        if(k == j && !(name %in% names)){
                            outNames <- c(outNames, paste("&nbsp;&nbsp;&nbsp;&nbsp;", format(signif(v, digits=3), justify="right", width=8, nsmall=3), " - ", name, sep=''))
                            names <- c(names, name)
                            break
                        }
                        k <- k + 1
                    }
                }
            }
            i <- i + 1
        }
    }
    title <- ""
    if(year == "."){
        title <- paste(f2, "vs.", f1, sep=' ')
    }
    else{
        title <- paste(year, f2, "vs.", f1, sep=' ')
    }
    title(main=title, cex=0.5)
    
    if(chartType == "anova"){
        c <- anova(field1, field2)
        intervals <- data.frame("Group 1" = c$g1,
                                "Change" = c$change, 
                                "Group 2" = c$g2, 
                                "Lower Bound" = c$lower,
                                "Upper Bound" = c$upper)
        HTML.title(paste(year, "ANOVA", f2, "vs.", f1, sep=' '), HR=3)
        HTMLInsertGraph(paste("../", filename, sep=''), WidthHTML=WIDTH, HeightHTML=HEIGHT, Align="left")
        HTML("&nbsp;&nbsp;<b>One-Way ANOVA Test:</b>")
        HTML(c$summary)
        HTML("&nbsp;&nbsp;<b>Intervals:</b>")
        HTML(data.frame(intervals), innerBorder=1, row.names=FALSE, align="left")
        if(length(outNames) > 0){
            HTML(paste(c("&nbsp;&nbsp;<b>Outliers:</b>", paste("", outNames, sep='<br />&nbsp;&nbsp;&nbsp;&nbsp;'))))
        }
    }
    else{
        c <- cor.test(field1, field2, alternative="two.sided", method="pearson")
        if(!is.null(c$conf.int)){
            lower <- round(c$conf.int[1], digits=3)
            upper <- round(c$conf.int[2], digits=3)
        }
        else{
            lower <- "NA"
            upper <- "NA"
        }
        statement <- corStrength(c)
        HTML.title(title, HR=3)
        HTMLInsertGraph(paste("../", filename, sep=''), WidthHTML=WIDTH, HeightHTML=HEIGHT, Align="left")
        HTML(paste(paste("&nbsp;&nbsp;<b>Pearson Correlation Test:</b>"),
                   paste("&nbsp;&nbsp;&nbsp;&nbsp;<b>Alternative Hypothesis:</b> true correlation is not equal to 0"),
                   paste("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>t</b> =", round(c$statistic, digits=3), ", <b>df</b> =", round(c$parameter, digits=3), ", <b>p-value</b> =", format(signif(c$p.value, digits=3), scientific=TRUE), sep=' '),
                   paste("&nbsp;&nbsp;&nbsp;&nbsp;<b>95 percent correlation confidence interval:</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", lower, upper, sep=' '), 
                   paste("&nbsp;&nbsp;&nbsp;&nbsp;<b>Correlation:</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", round(c$estimate, digits=3), sep=' '),
                   paste("&nbsp;&nbsp;&nbsp;&nbsp;There is", statement, "between", f1, "and", f2, sep=' '),
                   sep='<br />'
                  )
            )
        if(length(outNames) > 0){
            HTML(paste(c("&nbsp;&nbsp;<b>Outliers:</b>", paste("", outNames, sep='<br />&nbsp;&nbsp;&nbsp;&nbsp;'))))
        }
    }
}

renderHist <- function(field, f, year, prefix){
    filename <- paste("output/", year, "/charts/kern/", prefix, "/", f, ".png", sep='')
    setDev(filename)
    dens <- density(na.omit(unlist(field)))
    par(mar=c(5.1,4.1,6.5,2.1))
    title <- paste("Kernel Density plot of", year, prefix, f, sep=' ')
    HTML.title(title, HR=3)
    plot(dens, main=title)
    polygon(dens, col="darkgray", border="black")
    HTMLInsertGraph(paste("../", filename, sep=''), WidthHTML=WIDTH, HeightHTML=HEIGHT, Align="left")
    
    filename <- paste("output/", year, "/charts/hist/", prefix, "/", f, ".png", sep='')
    setDev(filename)
    title <- paste("Histogram of", year, prefix, f, sep=' ')
    HTML.title(title, HR=3)
    hist(na.omit(unlist(field)), breaks="Sturges", col="darkgray", xlab="f", main=title)
    HTMLInsertGraph(paste("../", filename, sep=''), WidthHTML=WIDTH, HeightHTML=HEIGHT, Align="left")
}

populateField <- function(field, f){
    cor <- c()
    i <- 1
    for(y in Years){
        for(f1 in fields){
            if(regexpr(paste(".*", field, "$", sep=''), f1) > -1){
                if(!all.is.identical(unlist(Datasets[[y]][f]))){
                    c <- cor.test(unlist(Datasets[[y]][field]), unlist(Datasets[[y]][f]), alternative="two.sided", method="pearson")
                    cor[i] = c$estimate     
                }
            }
        }
        i <- i+1
    }
    return(cor)
}

populate <- function(dataset, field){
    dataset <- NULL
    for(f in fields){
        col <- populateField(field, f)
        if(!is.null(dataset)){
            dataset[f] <- col
        }
        else{
            dataset <- data.frame(col)
            dataset[f] <- col
        }
    }
    return(dataset)
}

s <- summarizeGraph()

# Load Data
f<-for(type in config$types){
    Datasets <- list()
    fields <- c()
    groupFields <- c()
    for(y in Years){
        d <- read.table(paste("output/data/", type, "_", y, ".csv", sep=''),
                                    header=TRUE, sep=",", na.strings="NA", dec=".", strip.white=TRUE)
        Datasets[[y]] <- d
    }
    
    is.transformable <- function(field){
        for(t in config$transformable[[type]]){
            if(t == field){
                return(TRUE)
            }
        }
        return(FALSE)
    }
    
    explicitTransformation <- function(field){
        for(transformations in config$transformations[type]){
            for(transformation in transformations){
                if(transformation$x == field){
                    return(transformation$t)
                }
            }
        }
        return("")
    }

    transform <- function(){
        d <- Datasets[[length(Datasets)]]
        for(field in names(d)){
            s0 <- 0
            s1 <- 0
            s2 <- 0
            s3 <- 0
            s4 <- 0
            s5 <- 0
            for(y in Years){
                dataset <- Datasets[[y]]
                if(all.is.numeric(dataset[field]) &&
                   !all.is.identical(unlist(dataset[field]))){
                    t1 <- unlist(sqrt(dataset[field]))
                    t2 <- unlist(log(dataset[field]+1))
                    t3 <- unlist(log(dataset[field]+1)/log(10))
                    t4 <- unlist(dataset[field]^2)
                    t5 <- unlist(dataset[field]^-1)

                    r0 <- shapiro.test(unlist(dataset[field]))
                    r1 <- shapiro.test(t1)
                    r2 <- shapiro.test(t2)
                    r3 <- shapiro.test(t3)
                    r4 <- shapiro.test(t4)
                    if(all.is.finite(t5)){
                        r5 <- shapiro.test(t5)
                    }
                    
                    s0 <- s0 + r0$statistic
                    s1 <- s1 + r1$statistic
                    s2 <- s2 + r2$statistic
                    s3 <- s3 + r3$statistic
                    s4 <- s4 + r4$statistic
                    if(all.is.finite(t5)){
                        s5 <- s5 + r5$statistic
                    }
                }
            }
            
            if(all.is.numeric(d[field]) &&
               !all.is.identical(unlist(d[field]))){
                eT <- explicitTransformation(field)
                m <- -1
                if(eT == ""){
                    m <- max(c(s0, s1, s2, s3, s4, s5))
                }
                if(s0 == m || (!is.transformable(field) && eT == "") || eT == "IDENTITY"){
                    # do nothing (Identity)
                    fields <<- append(fields, field)
                }
                else if(s1 == m || eT == "SQRT"){
                    f <- paste("SQRT.", field, sep='')
                    fields <<- append(fields, f)
                    for(y in Years){
                        Datasets[[y]][f] <<- unlist(sqrt(Datasets[[y]][field]))
                    }
                }
                else if(s2 == m || eT == "LN"){
                    f <- paste("LN.", field, sep='')
                    fields <<- append(fields, f)
                    for(y in Years){
                        Datasets[[y]][f] <<- unlist(log(Datasets[[y]][field]+1))
                    }
                }
                else if(s3 == m || eT == "LOG"){
                    f <- paste("LOG.", field , sep='')
                    fields <<- append(fields, f)
                    for(y in Years){
                        Datasets[[y]][f] <<- unlist(log(Datasets[[y]][field]+1)/log(10))
                    }
                }
                else if(s4 == m || eT == "SQR"){
                    f <- paste("SQR.", field , sep='')
                    fields <<- append(fields, f)
                    for(y in Years){
                        Datasets[[y]][f] <<- unlist((Datasets[[y]][field])^2)
                    }
                }
                else if(s5 == m || eT == "INV"){
                    f <- paste("INV.", field , sep='')
                    fields <<- append(fields, f)
                    for(y in Years){
                        Datasets[[y]][f] <<- unlist((Datasets[[y]][field])^-1)
                    }
                }
            }
            else{
                groupFields <<- append(groupFields, field)
            }
        }
    }
    
    transform()

    Between     <- populate(Between, "Between")
    Closeness   <- populate(Closeness, "Closeness")
    PageRank    <- populate(PageRank, "PageRank")

    Dataset4 <- data.frame(Years)

    for(f in fields){
        Dataset4[paste("Between.", f, sep='')] <- Between[f]
        Dataset4[paste("Closeness.", f, sep='')] <- Closeness[f]
        Dataset4[paste("PageRank.", f, sep='')] <- PageRank[f]
    }

    # Create directories if they don't exist
    dir.create(file.path("output/charts"), showWarnings = FALSE)
    dir.create(file.path("output/charts/cor"), showWarnings = FALSE)
    dir.create(file.path(paste("output/charts/cor/", type, sep='')), showWarnings = FALSE)
    dir.create(file.path("output/charts/tests"), showWarnings = FALSE)
    dir.create(file.path(paste("output/charts/tests/", type, sep='')), showWarnings = FALSE)
    HTML.title("Correlation of Centralities over Time", HR=2)
    for(f in fields){
        renderCorChart(f, type)
    }
    
    HTML.title(paste(type, "Tests", sep=' '), HR=2)
    for(test in config$tests[[type]]){
        if(test$x == "Years" || test$x == "Time"){
            for(f1 in fields){
                if(regexpr(paste(".*", test$y, "$", sep=''), f1) > -1){
                    d <- c()
                    y <- c()
                    for(year in Years){
                        col <- applyOp(unlist(Datasets[[year]][f1]), test$yOp)
                        d <- c(d, col)
                        for(v in col){
                            y <- c(y, year)
                        }
                    }
                    runCorTest(y, d, test$x, f1, ".", type, test$type)
                    HTMLhr()
                }
            }
        }
    }

    for(year in Years){
        d <- Datasets[[year]]
        # Create directories if they don't exist
        dir.create(file.path(paste("output/", year, sep='')), showWarnings = FALSE)
        dir.create(file.path(paste("output/", year, "/charts", sep='')), showWarnings = FALSE)
        dir.create(file.path(paste("output/", year, "/charts/hist", sep='')), showWarnings = FALSE)
        dir.create(file.path(paste("output/", year, "/charts/hist/", type, sep='')), showWarnings = FALSE)
        dir.create(file.path(paste("output/", year, "/charts/kern", sep='')), showWarnings = FALSE)
        dir.create(file.path(paste("output/", year, "/charts/kern/", type, sep='')), showWarnings = FALSE)
        dir.create(file.path(paste("output/", year, "/charts/tests", sep='')), showWarnings = FALSE)
        dir.create(file.path(paste("output/", year, "/charts/tests/", type, sep='')), showWarnings = FALSE)
        
        HTML.title(paste(type, year, "Tests", sep=' '), HR=2)
        for(test in config$tests[[type]]){
            for(f1 in c(fields, groupFields)){
                if(regexpr(paste("^(.*\\.)?", test$x, "$", sep=''), f1) > -1){
                    for(f2 in c(fields, groupFields)){
                        if(regexpr(paste("^(.*\\.)?", test$y, "$", sep=''), f2) > -1){
                            runCorTest(unlist(d[f1]), unlist(d[f2]), f1, f2, year, type, test$type)
                            HTMLhr()
                            break
                        }
                    }
                    break
                }
            }
        }

        HTML.title(paste(type, year, "Distributions", sep=' '), HR=2)
        for(field in fields){
            renderHist(d[field], field, year, type)
        }
    }
}
d <- dev.off()
html <- HTMLEndFile()
html <- HTMLStop()
