<?php

function update541() {
tposts::i()->changed = thomepage::i()->postschanged;
thomepage::i()->postschanged();
}