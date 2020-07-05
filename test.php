.fave{
  width:65px;
  height:50px;
  background:#fff url(/images/posts/steps/twitter_fave.png) no-repeat;
  background-position:0 2px;cursor:pointer;
  -webkit-animation:fave 1s steps(55);
  animation:fave 1s steps(55)
}

.fave:hover,.fave.active{
  background-position:-3519px 2px;
  -webkit-transition:background 1s steps(55);
  transition:background 1s steps(55)
}

@-webkit-keyframes fave{
  0%  {
    background-position:0 2px
  }
  100%{
    background-position:-3519px 2px
  }

}
@keyframes fave{
  0%{
    background-position:0 2px
  }
  100%{
    background-position:-3519px 2px
  }

}

.fave.heart{
  width:58px;
  height:50px;
  background:#fff url(/images/posts/steps/heart.png) no-repeat;
  background-size:auto 55px;
  -webkit-animation:fave-heart 1s steps(28);
  animation:fave-heart 1s steps(28)
}
.fave.heart:hover,.fave.heart.active{
  background-position:-1540px 0;
  -webkit-transition:background 1s steps(28);
  transition:background 1s steps(28)
}



@-webkit-keyframes fave-heart{0%{background-position:0 0}100%{background-position:-1540px 0}}@keyframes fave-heart{0%{background-position:0 0}100%{background-position:-1540px 0}}