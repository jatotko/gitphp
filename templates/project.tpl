{*
 *  project.tpl
 *  gitphp: A PHP git repository browser
 *  Component: Project summary template
 *
 *  Copyright (C) 2009 Christopher Han <xiphux@gmail.com>
 *}

 {include file='header.tpl'}

 <div class="page_nav">
 {include file='nav.tpl' commit=$head current='summary'}
 <br /><br />
 </div>

 {include file='title.tpl'}

 {* Project brief *}
 <table cellspacing="0">
   <tr><td>{t}description{/t}</td><td>{$project->GetDescription()}</td></tr>
   <tr><td>{t}owner{/t}</td><td>{$project->GetOwner()}</td></tr>
   {if $head}
   <tr><td>{t}last change{/t}</td><td>{$head->GetCommitterEpoch()|date_format:"%a, %d %b %Y %H:%M:%S %z"}</td></tr>
   {/if}
   {if $project->GetCloneUrl()}
     <tr><td>{t}clone url{/t}</td><td>{$project->GetCloneUrl()}</td></tr>
   {/if}
   {if $project->GetPushUrl()}
     <tr><td>{t}push url{/t}</td><td>{$project->GetPushUrl()}</td></tr>
   {/if}
 </table>

 {if !$head}
   {include file='title.tpl' target='shortlog' disablelink=true}
 {else}
   {include file='title.tpl' target='shortlog'}
 {/if}

 {include file='shortloglist.tpl' source='summary'}
 
 {if $taglist}
  
  {include file='title.tpl' target='tags'}

  {include file='taglist.tpl' max=17}
   
 {/if}

 {if $headlist}

  {include file='title.tpl' target='heads'}

  {include file='headlist.tpl' max=17}

 {/if}

 {include file='footer.tpl'}

