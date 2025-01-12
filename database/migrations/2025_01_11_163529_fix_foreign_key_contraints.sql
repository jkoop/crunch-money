-- if and only if your database is older than Jan 11, 2025, you need to run this migration
-- to fix the foreign key constraints. This migration will recreate tables with the
-- correct foreign key constraints.

BEGIN TRANSACTION;

PRAGMA foreign_keys = OFF;

CREATE TABLE IF NOT EXISTS "_new_budgets" (
	"id" integer primary key autoincrement not null,
	"owner_id" integer not null references "users" ("id") on delete cascade on update restrict,
	"period_id" integer not null references "periods" ("id") on delete cascade on update restrict,
	"name" varchar not null,
	"slug" varchar not null,
	"created_at" datetime,
	"updated_at" datetime,
	"deleted_at" datetime,
	"amount" varchar not null,
	UNIQUE ("owner_id", "period_id", "slug")
);

INSERT INTO "_new_budgets" SELECT * FROM "budgets";
DROP TABLE "budgets";
ALTER TABLE "_new_budgets" RENAME TO "budgets";

CREATE TABLE IF NOT EXISTS "_new_funds" (
	"id" integer primary key autoincrement not null,
	"owner_id" integer not null references "users" ("id") on delete cascade on update restrict,
	"name" varchar not null,
	"slug" varchar not null,
	"created_at" datetime,
	"updated_at" datetime,
	UNIQUE ("owner_id", "slug")
);

INSERT INTO "_new_funds" SELECT * FROM "funds";
DROP TABLE "funds";
ALTER TABLE "_new_funds" RENAME TO "funds";

CREATE TABLE "_new_fund_period" (
	"fund_id" integer not null references "funds" ("id") on delete cascade on update restrict,
	"period_id" integer not null references "periods" ("id") on delete cascade on update restrict,
	"amount" varchar not null,
	PRIMARY KEY ("fund_id", "period_id")
);

INSERT INTO "_new_fund_period" SELECT * FROM "fund_period";
DROP TABLE "fund_period";
ALTER TABLE "_new_fund_period" RENAME TO "fund_period";

CREATE TABLE "_new_incomes" (
	"id" integer primary key autoincrement not null,
	"owner_id" integer not null references "users" ("id") on delete cascade on update restrict,
	"period_id" integer not null references "periods" ("id") on delete cascade on update restrict,
	"name" varchar not null,
	"amount" numeric not null,
	"created_at" datetime,
	"updated_at" datetime
);

INSERT INTO "_new_incomes" SELECT * FROM "incomes";
DROP TABLE "incomes";
ALTER TABLE "_new_incomes" RENAME TO "incomes";

CREATE TABLE "_new_periods" (
	"id" integer primary key autoincrement not null,
	"owner_id" integer not null references "users" ("id") on delete cascade on update restrict,
	"start" date not null,
	"end" date not null,
	"created_at" datetime,
	"updated_at" datetime,
	UNIQUE ("owner_id", "start")
);

INSERT INTO "_new_periods" SELECT * FROM "periods";
DROP TABLE "periods";
ALTER TABLE "_new_periods" RENAME TO "periods";

CREATE TABLE "_new_transactions" (
	"id" integer primary key autoincrement not null,
	"owner_id" integer not null references "users" ("id") on delete cascade on update restrict,
	"fund_id" integer references "funds" ("id") on delete cascade on update restrict,
	"budget_id" integer references "budgets" ("id") on delete cascade on update restrict,
	"period_id" integer not null references "periods" ("id") on delete cascade on update restrict,
	"description" varchar not null,
	"date" date not null,
	"amount" numeric not null,
	"is_system" tinyint(1) not null default '0',
	"created_at" datetime,
	"updated_at" datetime
);

INSERT INTO "_new_transactions" SELECT * FROM "transactions";
DROP TABLE "transactions";
ALTER TABLE "_new_transactions" RENAME TO "transactions";

COMMIT;
