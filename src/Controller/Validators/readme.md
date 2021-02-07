Info: don't use this logic, it's over complicated, there is new validator in the Forms folder
- easier to create,
- easier to manage,
- frontend js logic already works as it shares the same rules as here,

# Info
The problem is that the logic in form validator works explicitly with forms,
and the logic added here can be used both for forms and data send via DataProcessors,
it needs to be considered which validation rules should be used then as THIS one seems to be the most
suitable