options(repos=structure(c(CRAN="http://cran.us.r-project.org")))
dir.create(file.path("libs/R/"), showWarnings = FALSE)

is.installed <- function(mypkg) is.element(mypkg, installed.packages(lib.loc="libs/R/")[,1])

if(!is.installed('mvtnorm')){
    install.packages("mvtnorm", lib="libs/R/")
}
if(!is.installed('rjson')){
    install.packages("rjson", lib="libs/R/")
}
if(!is.installed('multcomp')){
    install.packages("multcomp", lib="libs/R/")
}
if(!is.installed('car')){
    install.packages("car", lib="libs/R/")
}
if(!is.installed('plotrix')){
    install.packages("plotrix", lib="libs/R/")
}
if(!is.installed('gtools')){
    install.packages("gtools", lib="libs/R/")
}
if(!is.installed('R2HTML')){
    install.packages("R2HTML", lib="libs/R/")
}
