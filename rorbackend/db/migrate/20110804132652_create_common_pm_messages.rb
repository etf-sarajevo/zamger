class CreateCommonPmMessages < ActiveRecord::Migration
  def change
    create_table :common_pm_messages do |t|
      t.integer :type, :limit => 4
      t.integer :scope, :limit => 4
      t.integer :to_id
      t.integer :from_id
      t.time :time
      t.integer :ref_id, :default => 0
      t.text :subject
      t.text :text

      # t.timestamps
    end
  end
end
