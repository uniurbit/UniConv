# Progetto condiviso 

Per utilizzare il progetto condiviso creare un riferimento al progetto

git remote add -f commmon https://enoliva@bitbucket.org/enoliva/unidemcommon.git

Aggiungere il subtree ad esempio nella cartella common e senza la storia

git subtree add --prefix common common master --squash

Per aggiornare 

git fetch common master

git subtree pull --prefix common common master --squash