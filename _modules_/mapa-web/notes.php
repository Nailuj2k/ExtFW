<?php if(1==2){ ?>
<style>
.notes {
  /* Showcase that the effect supports any backdrop */
  background: repeating-linear-gradient(-45deg, #ddd 0, #ddd 25%, white 0, white 50%) 0/6px 6px;
  box-sizing: border-box;
  padding: 1em;
  height: 100vh;
}
.notes .note {
  position: relative;
  display: inline-block;
  vertical-align: top;
  width: 15em;
  padding: 2em;
  margin: 0 1rem;
  color: white;
  font: 100%/1.6 Baskerville, Palatino, serif;
  border-radius: .5em;
  position: relative;
  background: #58a;
  /* Fallback */
  background: linear-gradient(-155deg, rgba(0, 0, 0, 0) 1.5em, #5588aa 0%);
  border-radius: .5em;
}
.notes .note::before {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  background: linear-gradient(to left bottom, rgba(0, 0, 0, 0) 50%, rgba(0, 0, 0, 0.2) 0%, rgba(0, 0, 0, 0.4)) 100% 0 no-repeat;
  width: 1.65507em;
  height: 3.5493em;
  transform: translateY(-1.89424em) rotate(-40deg);
  transform-origin: bottom right;
  border-bottom-left-radius: inherit;
  box-shadow: -0.2em 0.2em 0.3em -0.1em rgba(0, 0, 0, 0.15);
}

.notes .note + .notes .note {
  font-size: 130%;
  position: relative;
  background: #655;
  /* Fallback */
  background: linear-gradient(-110deg, rgba(0, 0, 0, 0) 2em, #665555 0%);
  border-radius: .5em;
}
.notes .note + .notes .note::before {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  background: linear-gradient(to left bottom, rgba(0, 0, 0, 0) 50%, rgba(0, 0, 0, 0.2) 0%, rgba(0, 0, 0, 0.4)) 100% 0 no-repeat;
  width: 5.84761em;
  height: 2.12836em;
  transform: translateY(3.71925em) rotate(50deg);
  transform-origin: bottom right;
  border-bottom-left-radius: inherit;
  box-shadow: -0.2em 0.2em 0.3em -0.1em rgba(0, 0, 0, 0.15);
}

.notes .note.green{
  width: 20em;
  position: relative;
  background: yellowgreen;
  /* Fallback */
  background: linear-gradient(-135deg, rgba(0, 0, 0, 0) 1.8em, #9acd32 0%);
  border-radius: .5em;
}
.notes .note.red{
  width: 20em;
  position: relative;
  background: #f33c2a;
  /* Fallback */
  background: linear-gradient(-135deg, rgba(0, 0, 0, 0) 1.8em, #f33c2a 0%);
  border-radius: .5em;
}
.notes .note.green::before,
.notes .note.red::before{
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  background: linear-gradient(to left bottom, rgba(0, 0, 0, 0) 50%, rgba(0, 0, 0, 0.2) 0%, rgba(0, 0, 0, 0.4)) 100% 0 no-repeat;
  width: 2.54558em;
  height: 2.54558em;
  transform: translateY(0em) rotate(0deg);
  transform-origin: bottom right;
  border-bottom-left-radius: inherit;
  box-shadow: -0.2em 0.2em 0.3em -0.1em rgba(0, 0, 0, 0.15);
}
</style>
<div class="notes">
<div class="note">“The only way to get rid of a temptation is to yield to it.”
— Oscar Wilde, The Picture of Dorian Gray</div>
<div class="note red">“The only way to get rid of a temptation is to yield to it.”
— Oscar Wilde, The Picture of Dorian Gray</div>
<div class="note green">“The only way to get rid of a temptation is to yield to it. Resist it, and your soul grows sick with longing for the things it has forbidden to itself, with desire for what its monstrous laws have made monstrous and unlawful.”
— Oscar Wilde, The Picture of Dorian Gray</div> 
</div>

<?php } ?>